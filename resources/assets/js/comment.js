"use strict";

// @flow

declare var add_like_path;
declare var update_path;
declare var fetch_path;
declare var user_id;
declare var comments;
declare var twttr;
declare var Vue;

const twitter_embed = `<blockquote class="twitter-tweet"><a href="$url"></a></blockquote>`;
const link_html = `<a href="{url}" target="_new">{url}</a>`;
const reg = /^https?:\/\/twitter.com\/(.*)\/(status|statuses)\/(\d+)$/;
const reg_link = /(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]\.[^\s]{2,})/;

class Comment {
    _comment: any;
    id: number;
    user_id: string;
    comment_id: number;
    comment: string;
    pre_comment: string;
    like_form_id: string;
    count: number;
    edit: boolean;
    unsent: boolean;
    saving: boolean;
    success_msg: string;
    error_msg: string;

    constructor(id, comment) {
        this._comment = comment;
        this.id = id;
        this.user_id = comment.user_id;
        this.comment_id = comment.comment_id;
        this.comment = comment.comment;
        this.pre_comment = comment.comment;
        this.like_form_id = 'like-' + comment.comment_id;
        this.count = comment.like_count;
        this.edit = false;
        this.unsent = false;
        this.saving = false;
        this.success_msg = '';
        this.error_msg = '';
    }
}

function build_comment(comment) {
    let comment_str = comment.split('\n');
    // twitter-linkを検出
    const _this = this;
    comment_str = comment_str.map(function (value) {
        if (reg.test(value)) {
            return twitter_embed.replace('$url', value);
        } else if (reg_link.test(value)) {
            const urls = value.match(reg_link);
            if (urls && urls.length > 0) {
                return value.replace(urls[0], link_html.replace(/{url}/g, urls[0]));
            }
        }
        return value;
    });
    // コメントを結合
    return comment_str.join('<br/>');
}

let top_comment = document.getElementById('top_comment');
if (top_comment && top_comment.dataset) {
    top_comment.innerHTML = build_comment(top_comment.dataset.comment);
}

const initial_comment = Object.keys(comments || []).map((key, index) => {
    return new Comment(key, comments[key]);
});
const last_id = initial_comment[initial_comment.length - 1] && initial_comment[initial_comment.length - 1].comment_id || Number.MAX_SAFE_INTEGER;

const vm = new Vue({
    delimiters: ['${', '}'],
    el: '#js-comments',
    data: {
        comments: initial_comment,
        error_msg: '',
        last_id: last_id,
        loading: false
    },
    created: function () {
        if (this.comments.length === 0) {
            this.fetchComment();
        }
    },
    updated: function () {
        this.comments.map((comment, index) => {
            twttr.widgets.load(document.getElementById('c' + comment.comment_id));
        });
    },
    methods: {
        fetchComment: function () {
            if (this.loading) {
                return;
            }
            this.loading = true;

            fetch(fetch_path + this.last_id, {
                method: 'GET',
                headers: {'X-Requested-With': 'XMLHttpRequest'}
            }).then((response) => {
                if (!response.ok) {
                    throw Error(response.statusText);
                }
                return response.json();
            }).then((response) => {
                this.error_msg = '';

                const new_comment = Object.keys(response).map((key, index) => {
                    return new Comment(key, response[key]);
                });
                this.comments.push(...new_comment);
                this.last_id = this.comments[this.comments.length - 1].comment_id;
            }).catch((error) => {
                this.error_msg = 'コメントを取得できませんでした。';
            }).finally(() => {
                this.loading = false;
            });
        },
        isOwned: function (id) {
            if (user_id === "0") {
                return false;
            }
            return id === user_id;
        },
        editing: function (comment) {
            return comment.edit;
        },
        saving: function (comment) {
            return comment.saving;
        },
        soudane: function (comment) {
            return 'そうだね ×' + comment.count;
        },
        has_error: function (comment) {
            return comment.error_msg !== '';
        },
        has_message: function (comment) {
            return comment.success_msg !== '';
        },
        comment_computed: build_comment,
        // edit
        editStart: function (comment) {
            comment.edit = true;
            comment.pre_comment = comment.comment;
            comment.success_msg = '';
        },
        editCancel: function (comment, event) {
            event.preventDefault();
            comment.edit = false;
            comment.comment = comment.pre_comment;
            comment.error_msg = '';
        },
        editEnd: function (comment, event) {
            event.preventDefault();
            comment.saving = true;
            comment.error_msg = '';

            const edit_dom = document.getElementById(event.target.id);
            if (!edit_dom) {
                return;
            }
            const edit_form = edit_dom.parentElement;
            if (!(edit_form instanceof HTMLFormElement)) {
                return;
            }

            fetch(update_path, {
                method: 'POST',
                headers: {'X-Requested-With': 'XMLHttpRequest'},
                body: new FormData(edit_form)
            })
                .then((response) => {
                    if (!response.ok) {
                        throw Error(response.statusText);
                    }
                    comment.error_msg = '';
                    comment.success_msg = 'コメントを保存しました。';
                    comment.edit = false;
                })
                .catch((e) => {
                    comment.edit = true;
                    comment.error_msg = '保存できませんでした。';
                })
                .finally((e) => {
                    comment.saving = false;
                });
        },
        //---------
        // like
        addLike: function (comment) {
            if (comment.unsent) {
                return;
            }
            const like_dom = document.getElementById(comment.like_form_id);
            if (!(like_dom instanceof HTMLFormElement)) {
                return;
            }

            const form = new FormData(like_dom);
            this.plus1(form, comment);
            comment.error_msg = '';
        },
        plus1: function (form, comment) {
            const _this = this;
            fetch(add_like_path, {
                method: 'POST',
                headers: {'X-Requested-With': 'XMLHttpRequest'},
                body: form
            })
                .then(function (response) {
                        if (!response.ok) {
                            throw Error(response.statusText);
                        }
                        _this.updateCount(comment);
                        _this.disabledButton(comment);
                    }
                )
                .catch((e) => {
                    comment.error_msg = '「そうだね」できませんでした。';
                });
        },
        updateCount: function (comment) {
            comment.count = (parseInt(comment.count)) + 1;
        },
        disabledButton: function (comment) {
            comment.unsent = true;
        },
        //---------
        // delete
        deleteConfirm: function (event) {
            const answer = confirm('このコメントを削除しますか？');
            if (answer === false) {
                event.preventDefault();
            }
        },
        //---------
        // scroll
        scrolling: function (event) {
            const body = document.body;
            const html = document.documentElement;
            if (!body || !html) {
                return;
            }
            const scrollTop = body.scrollTop || html.scrollTop;
            const scrollBottom = html.scrollHeight - scrollTop - html.clientHeight;
            if (scrollBottom <= 0 && !this.loading) {
                this.fetchComment();
            }
        }
    },
    beforeMount: function () {
        window.addEventListener('scroll', this.scrolling)
    },
    afterMount: function () {
        window.removeEventListener('scroll', this.scrolling)
    },
});