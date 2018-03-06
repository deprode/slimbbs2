"use strict";

let comments = document.querySelectorAll('section.js-comment');
Array.from(comments).forEach((comment) => {
    const comment_str = document.getElementById(comment.id).dataset.comment;
    const like_form = document.getElementById(comment.id).querySelectorAll('form.js-like')[0];
    const twitter_embed = `<blockquote class="twitter-tweet"><a href="$url"></a></blockquote>`;

    new Vue({
        delimiters: ['${', '}'],
        el: '#' + comment.id,
        data: {
            form_id: comment.id,
            comment: comment_str || '',
            edit: false,
            pre_comment: comment_str,
            like_form_id: like_form ? like_form.id : '',
            count: like_form ? document.getElementById(like_form.id).dataset.like : 0,
            unsent: false,
            error_msg: ''
        },
        computed: {
            editing: function () {
                return this.edit;
            },
            soudane: function () {
                return 'そうだね ×' + this.count;
            },
            has_error: function () {
                return this.error_msg !== '';
            },
            comment_computed: function () {
                const reg = /^https?:\/\/twitter.com\/(.*)\/(status|statuses)\/(\d+)$/;
                let comments = this.comment.split('\n');
                // twitter-linkを検出
                const _this = this;
                comments = comments.map(function (value) {
                    if (reg.test(value)) {
                        return twitter_embed.replace('$url', value);
                    }
                    return value;
                });
                // コメントを結合
                return comments.join('<br/>');
            }
        },
        methods: {
            // edit
            editStart: function () {
                this.edit = true;
                this.pre_comment = this.comment;
            },
            editCancel: function (event) {
                event.preventDefault();
                this.edit = false;
                this.comment = this.pre_comment;
                this.error_msg = '';
            },
            editEnd: function (event) {
                event.preventDefault();
                this.edit = false;
                this.error_msg = '';

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
                        this.error_msg = '';
                    })
                    .catch((e) => {
                        this.edit = true;
                        this.comment = this.pre_comment;
                        this.error_msg = '保存できませんでした。';
                    });

                this.pre_comment = this.comment;
            },
            //---------
            // like
            addLike: function (event) {
                if (this.unsent) {
                    return;
                }
                const form = new FormData(document.getElementById(this.like_form_id));
                this.plus1(form);
                this.error_msg = '';
            },
            plus1: function (form) {
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
                            _this.updateCount();
                            _this.disabledButton();
                        }
                    )
                    .catch((e) => {
                        this.error_msg = '「そうだね」できませんでした。';
                    });
            },
            updateCount: function () {
                this.count = (parseInt(this.count)) + 1;
            },
            disabledButton: function () {
                this.unsent = true;
            },
            //---------
            // delete
            deleteConfirm: function (event) {
                const answer = confirm('このコメントを削除しますか？');
                if (answer === false) {
                    event.preventDefault();
                }
            }
        }
    });
});