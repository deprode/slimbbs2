"use strict";

const twitter_embed = `<blockquote class="twitter-tweet"><a href="$url"></a></blockquote>`;
const link_html = `<a href="{url}" target="_new">{url}</a>`;
const reg = /^https?:\/\/twitter.com\/(.*)\/(status|statuses)\/(\d+)$/;
const reg_link = /(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]\.[^\s]{2,})/;

window.twttr = (function (d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0],
        t = window.twttr || {};
    if (d.getElementById(id)) return t;
    js = d.createElement(s);
    js.id = id;
    js.src = "https://platform.twitter.com/widgets.js";
    fjs.parentNode.insertBefore(js, fjs);

    t._e = [];
    t.ready = function (f) {
        t._e.push(f);
    };

    return t;
}(document, "script", "twitter-wjs"));

class Comment {
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
            return link_html.replace(/{url}/g, value);
        }
        return value;
    });
    // コメントを結合
    return comment_str.join('<br/>');
}

document.getElementById('top_comment').innerHTML = build_comment(document.getElementById('top_comment').dataset.comment);

const vm = new Vue({
    delimiters: ['${', '}'],
    el: '#js-comments',
    data: {
        comments: [],
        error_msg: '',
        last_id: Number.MAX_SAFE_INTEGER,
        loading: false
    },
    created: function () {
        this.fetchComment();
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
            return id === user_id;
        },
        editing: function (comment) {
            return comment.edit;
        },
        soudane: function (comment) {
            return 'そうだね ×' + comment.count;
        },
        has_error: function (comment) {
            return comment.error_msg !== '';
        },
        comment_computed: build_comment,
        // edit
        editStart: function (comment) {
            comment.edit = true;
            comment.pre_comment = comment.comment;
        },
        editCancel: function (comment, event) {
            event.preventDefault();
            comment.edit = false;
            comment.comment = comment.pre_comment;
            comment.error_msg = '';
        },
        editEnd: function (comment, event) {
            event.preventDefault();
            comment.edit = false;
            comment.error_msg = '';

            const form = document.getElementById(event.target.id).parentElement;

            fetch(update_path, {
                method: 'POST',
                headers: {'X-Requested-With': 'XMLHttpRequest'},
                body: new FormData(form)
            })
                .then((response) => {
                    if (!response.ok) {
                        throw Error(response.statusText);
                    }
                    comment.error_msg = '';
                })
                .catch((e) => {
                    comment.edit = true;
                    comment.comment = comment.pre_comment;
                    comment.error_msg = '保存できませんでした。';
                });

            comment.pre_comment = comment.comment;
        },
        //---------
        // like
        addLike: function (comment) {
            if (comment.unsent) {
                return;
            }
            const form = new FormData(document.getElementById(comment.like_form_id));
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