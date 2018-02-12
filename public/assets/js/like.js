"use strict";

let forms = document.querySelectorAll('form.js-like');
Array.from(forms).forEach((form) => {
    new Vue({
        delimiters: ['${', '}'],
        el: '#' + form.id,
        data: {
            form_id: form.id,
            count: document.getElementById(form.id).dataset.like,
            unsent: false
        },
        computed: {
            soudane: function () {
                return 'そうだね ×' + this.count;
            }
        },
        methods: {
            addLike: function (event) {
                if (this.unsent) {
                    return;
                }
                const form = new FormData(document.getElementById(this.form_id));
                this.plus1(form);
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
                        console.error(e);
                    });
            },
            updateCount: function () {
                this.count = (parseInt(this.count)) + 1;
            },
            disabledButton: function () {
                this.unsent = true;
            }
        }
    });
});