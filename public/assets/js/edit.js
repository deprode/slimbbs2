"use strict";

let comments = document.querySelectorAll('section.js-comment');
Array.from(comments).forEach((comment) => {
    const comment_str = document.getElementById(comment.id).dataset.comment;
    new Vue({
        delimiters: ['${', '}'],
        el: '#' + comment.id,
        data: {
            form_id: comment.id,
            comment: comment_str,
            edit: false,
            pre_comment: comment_str
        },
        computed: {
            editing: function () {
                return this.edit;
            }
        },
        methods: {
            editStart: function () {
                this.edit = true;
                this.pre_comment = this.comment;
            },
            editCancel: function (event) {
                event.preventDefault();
                this.edit = false;
                this.comment = this.pre_comment;
            },
            editEnd: function (event) {
                event.preventDefault();
                this.edit = false;

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
                    })
                    .catch((e) => {
                        console.error(e);
                        this.edit = true;
                        this.comment = this.pre_comment;
                    });

                this.pre_comment = this.comment;
            }
        }
    });
});
