(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
"use strict";

function _toConsumableArray(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } else { return Array.from(arr); } }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var twitter_embed = '<blockquote class="twitter-tweet"><a href="$url"></a></blockquote>';
var link_html = '<a href="{url}" target="_new">{url}</a>';
var reg = /^https?:\/\/twitter.com\/(.*)\/(status|statuses)\/(\d+)(\?s=\d+)*$/;
var reg_link = /(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]\.[^\s]{2,})/;

var Comment = function Comment(id, comment) {
    _classCallCheck(this, Comment);

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
};

function build_comment(comment) {
    var comment_str = comment.split('\n');
    // twitter-linkを検出
    var _this = this;
    comment_str = comment_str.map(function (value) {
        if (reg.test(value)) {
            return twitter_embed.replace('$url', value);
        } else if (reg_link.test(value)) {
            var urls = value.match(reg_link);
            if (urls && urls.length > 0) {
                return value.replace(urls[0], link_html.replace(/{url}/g, urls[0]));
            }
        }
        return value;
    });
    // コメントを結合
    return comment_str.join('<br/>');
}

var top_comment = document.getElementById('top_comment');
if (top_comment && top_comment.dataset) {
    top_comment.innerHTML = build_comment(top_comment.dataset.comment);
}

var initial_comment = Object.keys(comments || []).map(function (key, index) {
    return new Comment(key, comments[key]);
});
var last_id = initial_comment[initial_comment.length - 1] && initial_comment[initial_comment.length - 1].comment_id || Number.MAX_SAFE_INTEGER;

var vm = new Vue({
    delimiters: ['${', '}'],
    el: '#js-comments',
    data: {
        comments: initial_comment,
        error_msg: '',
        last_id: last_id,
        loading: false
    },
    created: function created() {
        if (this.comments.length === 0) {
            this.fetchComment();
        }
    },
    updated: function updated() {
        this.comments.map(function (comment, index) {
            twttr.widgets.load(document.getElementById('c' + comment.comment_id));
        });
    },
    methods: {
        fetchComment: function fetchComment() {
            var _this2 = this;

            if (this.loading) {
                return;
            }
            this.loading = true;

            fetch(fetch_path + this.last_id, {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).then(function (response) {
                if (!response.ok) {
                    throw Error(response.statusText);
                }
                return response.json();
            }).then(function (response) {
                var _comments;

                _this2.error_msg = '';

                var new_comment = Object.keys(response).map(function (key, index) {
                    return new Comment(key, response[key]);
                });
                (_comments = _this2.comments).push.apply(_comments, _toConsumableArray(new_comment));
                _this2.last_id = _this2.comments[_this2.comments.length - 1].comment_id;
            }).catch(function (error) {
                _this2.error_msg = 'コメントを取得できませんでした。';
            }).finally(function () {
                _this2.loading = false;
            });
        },
        isOwned: function isOwned(id) {
            if (user_id === "0") {
                return false;
            }
            return id === user_id;
        },
        editing: function editing(comment) {
            return comment.edit;
        },
        saving: function saving(comment) {
            return comment.saving;
        },
        soudane: function soudane(comment) {
            return 'そうだね ×' + comment.count;
        },
        has_error: function has_error(comment) {
            return comment.error_msg !== '';
        },
        has_message: function has_message(comment) {
            return comment.success_msg !== '';
        },
        comment_computed: build_comment,
        // edit
        editStart: function editStart(comment) {
            comment.edit = true;
            comment.pre_comment = comment.comment;
            comment.success_msg = '';
        },
        editCancel: function editCancel(comment, event) {
            event.preventDefault();
            comment.edit = false;
            comment.comment = comment.pre_comment;
            comment.error_msg = '';
        },
        editEnd: function editEnd(comment, event) {
            event.preventDefault();
            comment.saving = true;
            comment.error_msg = '';

            var edit_dom = document.getElementById(event.target.id);
            if (!edit_dom) {
                return;
            }
            var edit_form = edit_dom.parentElement;
            if (!(edit_form instanceof HTMLFormElement)) {
                return;
            }

            fetch(update_path, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(edit_form)
            }).then(function (response) {
                if (!response.ok) {
                    throw Error(response.statusText);
                }
                comment.error_msg = '';
                comment.success_msg = 'コメントを保存しました。';
                comment.edit = false;
            }).catch(function (e) {
                comment.edit = true;
                comment.error_msg = '保存できませんでした。';
            }).finally(function (e) {
                comment.saving = false;
            });
        },
        //---------
        // like
        addLike: function addLike(comment) {
            if (comment.unsent) {
                return;
            }
            var like_dom = document.getElementById(comment.like_form_id);
            if (!(like_dom instanceof HTMLFormElement)) {
                return;
            }

            var form = new FormData(like_dom);
            this.plus1(form, comment);
            comment.error_msg = '';
        },
        plus1: function plus1(form, comment) {
            var _this = this;
            fetch(add_like_path, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: form
            }).then(function (response) {
                if (!response.ok) {
                    throw Error(response.statusText);
                }
                _this.updateCount(comment);
                _this.disabledButton(comment);
            }).catch(function (e) {
                comment.error_msg = '「そうだね」できませんでした。';
            });
        },
        updateCount: function updateCount(comment) {
            comment.count = parseInt(comment.count) + 1;
        },
        disabledButton: function disabledButton(comment) {
            comment.unsent = true;
        },
        //---------
        // delete
        deleteConfirm: function deleteConfirm(event) {
            var answer = confirm('このコメントを削除しますか？');
            if (answer === false) {
                event.preventDefault();
            }
        },
        //---------
        // scroll
        scrolling: function scrolling(event) {
            var body = document.body;
            var html = document.documentElement;
            if (!body || !html) {
                return;
            }
            var scrollTop = body.scrollTop || html.scrollTop;
            var scrollBottom = html.scrollHeight - scrollTop - html.clientHeight;
            if (scrollBottom <= 0 && !this.loading) {
                this.fetchComment();
            }
        }
    },
    beforeMount: function beforeMount() {
        window.addEventListener('scroll', this.scrolling);
    },
    afterMount: function afterMount() {
        window.removeEventListener('scroll', this.scrolling);
    }
});

},{}],2:[function(require,module,exports){
"use strict";

// https://dev.twitter.com/web/javascript/loading
window.twttr = function (d, s, id) {
    var js,
        fjs = d.getElementsByTagName(s)[0],
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
}(document, "script", "twitter-wjs");

},{}]},{},[2,1])
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJyZXNvdXJjZXMvYXNzZXRzL2pzL2NvbW1lbnQuanMiLCJyZXNvdXJjZXMvYXNzZXRzL2pzL3R3dHRyLmpzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBO0FDQUE7Ozs7OztBQVlBLElBQU0sb0ZBQU47QUFDQSxJQUFNLHFEQUFOO0FBQ0EsSUFBTSxNQUFNLG9FQUFaO0FBQ0EsSUFBTSxXQUFXLGlJQUFqQjs7SUFFTSxPLEdBZUYsaUJBQVksRUFBWixFQUFnQixPQUFoQixFQUF5QjtBQUFBOztBQUNyQixTQUFLLFFBQUwsR0FBZ0IsT0FBaEI7QUFDQSxTQUFLLEVBQUwsR0FBVSxFQUFWO0FBQ0EsU0FBSyxPQUFMLEdBQWUsUUFBUSxPQUF2QjtBQUNBLFNBQUssVUFBTCxHQUFrQixRQUFRLFVBQTFCO0FBQ0EsU0FBSyxPQUFMLEdBQWUsUUFBUSxPQUF2QjtBQUNBLFNBQUssV0FBTCxHQUFtQixRQUFRLE9BQTNCO0FBQ0EsU0FBSyxZQUFMLEdBQW9CLFVBQVUsUUFBUSxVQUF0QztBQUNBLFNBQUssS0FBTCxHQUFhLFFBQVEsVUFBckI7QUFDQSxTQUFLLElBQUwsR0FBWSxLQUFaO0FBQ0EsU0FBSyxNQUFMLEdBQWMsS0FBZDtBQUNBLFNBQUssTUFBTCxHQUFjLEtBQWQ7QUFDQSxTQUFLLFdBQUwsR0FBbUIsRUFBbkI7QUFDQSxTQUFLLFNBQUwsR0FBaUIsRUFBakI7QUFDSCxDOztBQUdMLFNBQVMsYUFBVCxDQUF1QixPQUF2QixFQUFnQztBQUM1QixRQUFJLGNBQWMsUUFBUSxLQUFSLENBQWMsSUFBZCxDQUFsQjtBQUNBO0FBQ0EsUUFBTSxRQUFRLElBQWQ7QUFDQSxrQkFBYyxZQUFZLEdBQVosQ0FBZ0IsVUFBVSxLQUFWLEVBQWlCO0FBQzNDLFlBQUksSUFBSSxJQUFKLENBQVMsS0FBVCxDQUFKLEVBQXFCO0FBQ2pCLG1CQUFPLGNBQWMsT0FBZCxDQUFzQixNQUF0QixFQUE4QixL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O0FBRUEsTUFBRSxFQUFGLEdBQU8sRUFBUDtBQUNBLE1BQUUsS0FBRixHQUFVLFVBQVUsQ0FBVixFQUFhO0FBQ25CLFVBQUUsRUFBRixDQUFLLElBQUwsQ0FBVSxDQUFWO0FBQ0gsS0FGRDs7QUFJQSxXQUFPLENBQVA7QUFDSCxDQWZlLENBZWQsUUFmYyxFQWVKLFFBZkksRUFlTSxhQWZOLENBQWhCIiwiZmlsZSI6ImdlbmVyYXRlZC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzQ29udGVudCI6WyIoZnVuY3Rpb24oKXtmdW5jdGlvbiByKGUsbix0KXtmdW5jdGlvbiBvKGksZil7aWYoIW5baV0pe2lmKCFlW2ldKXt2YXIgYz1cImZ1bmN0aW9uXCI9PXR5cGVvZiByZXF1aXJlJiZyZXF1aXJlO2lmKCFmJiZjKXJldHVybiBjKGksITApO2lmKHUpcmV0dXJuIHUoaSwhMCk7dmFyIGE9bmV3IEVycm9yKFwiQ2Fubm90IGZpbmQgbW9kdWxlICdcIitpK1wiJ1wiKTt0aHJvdyBhLmNvZGU9XCJNT0RVTEVfTk9UX0ZPVU5EXCIsYX12YXIgcD1uW2ldPXtleHBvcnRzOnt9fTtlW2ldWzBdLmNhbGwocC5leHBvcnRzLGZ1bmN0aW9uKHIpe3ZhciBuPWVbaV1bMV1bcl07cmV0dXJuIG8obnx8cil9LHAscC5leHBvcnRzLHIsZSxuLHQpfXJldHVybiBuW2ldLmV4cG9ydHN9Zm9yKHZhciB1PVwiZnVuY3Rpb25cIj09dHlwZW9mIHJlcXVpcmUmJnJlcXVpcmUsaT0wO2k8dC5sZW5ndGg7aSsrKW8odFtpXSk7cmV0dXJuIG99cmV0dXJuIHJ9KSgpIiwiXCJ1c2Ugc3RyaWN0XCI7XG5cbi8vIEBmbG93XG5cbmRlY2xhcmUgdmFyIGFkZF9saWtlX3BhdGg7XG5kZWNsYXJlIHZhciB1cGRhdGVfcGF0aDtcbmRlY2xhcmUgdmFyIGZldGNoX3BhdGg7XG5kZWNsYXJlIHZhciB1c2VyX2lkO1xuZGVjbGFyZSB2YXIgY29tbWVudHM7XG5kZWNsYXJlIHZhciB0d3R0cjtcbmRlY2xhcmUgdmFyIFZ1ZTtcblxuY29uc3QgdHdpdHRlcl9lbWJlZCA9IGA8YmxvY2txdW90ZSBjbGFzcz1cInR3aXR0ZXItdHdlZXRcIj48YSBocmVmPVwiJHVybFwiPjwvYT48L2Jsb2NrcXVvdGU+YDtcbmNvbnN0IGxpbmtfaHRtbCA9IGA8YSBocmVmPVwie3VybH1cIiB0YXJnZXQ9XCJfbmV3XCI+e3VybH08L2E+YDtcbmNvbnN0IHJlZyA9IC9eaHR0cHM/OlxcL1xcL3R3aXR0ZXIuY29tXFwvKC4qKVxcLyhzdGF0dXN8c3RhdHVzZXMpXFwvKFxcZCspKFxcP3M9XFxkKykqJC87XG5jb25zdCByZWdfbGluayA9IC8oaHR0cHM/OlxcL1xcLyg/Ond3d1xcLnwoPyF3d3cpKVthLXpBLVowLTldW2EtekEtWjAtOS1dK1thLXpBLVowLTldXFwuW15cXHNdezIsfXxodHRwcz86XFwvXFwvKD86d3d3XFwufCg/IXd3dykpW2EtekEtWjAtOV1cXC5bXlxcc117Mix9KS87XG5cbmNsYXNzIENvbW1lbnQge1xuICAgIF9jb21tZW50OiBhbnk7XG4gICAgaWQ6IG51bWJlcjtcbiAgICB1c2VyX2lkOiBzdHJpbmc7XG4gICAgY29tbWVudF9pZDogbnVtYmVyO1xuICAgIGNvbW1lbnQ6IHN0cmluZztcbiAgICBwcmVfY29tbWVudDogc3RyaW5nO1xuICAgIGxpa2VfZm9ybV9pZDogc3RyaW5nO1xuICAgIGNvdW50OiBudW1iZXI7XG4gICAgZWRpdDogYm9vbGVhbjtcbiAgICB1bnNlbnQ6IGJvb2xlYW47XG4gICAgc2F2aW5nOiBib29sZWFuO1xuICAgIHN1Y2Nlc3NfbXNnOiBzdHJpbmc7XG4gICAgZXJyb3JfbXNnOiBzdHJpbmc7XG5cbiAgICBjb25zdHJ1Y3RvcihpZCwgY29tbWVudCkge1xuICAgICAgICB0aGlzLl9jb21tZW50ID0gY29tbWVudDtcbiAgICAgICAgdGhpcy5pZCA9IGlkO1xuICAgICAgICB0aGlzLnVzZXJfaWQgPSBjb21tZW50LnVzZXJfaWQ7XG4gICAgICAgIHRoaXMuY29tbWVudF9pZCA9IGNvbW1lbnQuY29tbWVudF9pZDtcbiAgICAgICAgdGhpcy5jb21tZW50ID0gY29tbWVudC5jb21tZW50O1xuICAgICAgICB0aGlzLnByZV9jb21tZW50ID0gY29tbWVudC5jb21tZW50O1xuICAgICAgICB0aGlzLmxpa2VfZm9ybV9pZCA9ICdsaWtlLScgKyBjb21tZW50LmNvbW1lbnRfaWQ7XG4gICAgICAgIHRoaXMuY291bnQgPSBjb21tZW50Lmxpa2VfY291bnQ7XG4gICAgICAgIHRoaXMuZWRpdCA9IGZhbHNlO1xuICAgICAgICB0aGlzLnVuc2VudCA9IGZhbHNlO1xuICAgICAgICB0aGlzLnNhdmluZyA9IGZhbHNlO1xuICAgICAgICB0aGlzLnN1Y2Nlc3NfbXNnID0gJyc7XG4gICAgICAgIHRoaXMuZXJyb3JfbXNnID0gJyc7XG4gICAgfVxufVxuXG5mdW5jdGlvbiBidWlsZF9jb21tZW50KGNvbW1lbnQpIHtcbiAgICBsZXQgY29tbWVudF9zdHIgPSBjb21tZW50LnNwbGl0KCdcXG4nKTtcbiAgICAvLyB0d2l0dGVyLWxpbmvjgpLmpJzlh7pcbiAgICBjb25zdCBfdGhpcyA9IHRoaXM7XG4gICAgY29tbWVudF9zdHIgPSBjb21tZW50X3N0ci5tYXAoZnVuY3Rpb24gKHZhbHVlKSB7XG4gICAgICAgIGlmIChyZWcudGVzdCh2YWx1ZSkpIHtcbiAgICAgICAgICAgIHJldHVybiB0d2l0dGVyX2VtYmVkLnJlcGxhY2UoJyR1cmwnLCB2YWx1ZSk7XG4gICAgICAgIH0gZWxzZSBpZiAocmVnX2xpbmsudGVzdCh2YWx1ZSkpIHtcbiAgICAgICAgICAgIGNvbnN0IHVybHMgPSB2YWx1ZS5tYXRjaChyZWdfbGluayk7XG4gICAgICAgICAgICBpZiAodXJscyAmJiB1cmxzLmxlbmd0aCA+IDApIHtcbiAgICAgICAgICAgICAgICByZXR1cm4gdmFsdWUucmVwbGFjZSh1cmxzWzBdLCBsaW5rX2h0bWwucmVwbGFjZSgve3VybH0vZywgdXJsc1swXSkpO1xuICAgICAgICAgICAgfVxuICAgICAgICB9XG4gICAgICAgIHJldHVybiB2YWx1ZTtcbiAgICB9KTtcbiAgICAvLyDjgrPjg6Hjg7Pjg4jjgpLntZDlkIhcbiAgICByZXR1cm4gY29tbWVudF9zdHIuam9pbignPGJyLz4nKTtcbn1cblxubGV0IHRvcF9jb21tZW50ID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3RvcF9jb21tZW50Jyk7XG5pZiAodG9wX2NvbW1lbnQgJiYgdG9wX2NvbW1lbnQuZGF0YXNldCkge1xuICAgIHRvcF9jb21tZW50LmlubmVySFRNTCA9IGJ1aWxkX2NvbW1lbnQodG9wX2NvbW1lbnQuZGF0YXNldC5jb21tZW50KTtcbn1cblxuY29uc3QgaW5pdGlhbF9jb21tZW50ID0gT2JqZWN0LmtleXMoY29tbWVudHMgfHwgW10pLm1hcCgoa2V5LCBpbmRleCkgPT4ge1xuICAgIHJldHVybiBuZXcgQ29tbWVudChrZXksIGNvbW1lbnRzW2tleV0pO1xufSk7XG5jb25zdCBsYXN0X2lkID0gaW5pdGlhbF9jb21tZW50W2luaXRpYWxfY29tbWVudC5sZW5ndGggLSAxXSAmJiBpbml0aWFsX2NvbW1lbnRbaW5pdGlhbF9jb21tZW50Lmxlbmd0aCAtIDFdLmNvbW1lbnRfaWQgfHwgTnVtYmVyLk1BWF9TQUZFX0lOVEVHRVI7XG5cbmNvbnN0IHZtID0gbmV3IFZ1ZSh7XG4gICAgZGVsaW1pdGVyczogWyckeycsICd9J10sXG4gICAgZWw6ICcjanMtY29tbWVudHMnLFxuICAgIGRhdGE6IHtcbiAgICAgICAgY29tbWVudHM6IGluaXRpYWxfY29tbWVudCxcbiAgICAgICAgZXJyb3JfbXNnOiAnJyxcbiAgICAgICAgbGFzdF9pZDogbGFzdF9pZCxcbiAgICAgICAgbG9hZGluZzogZmFsc2VcbiAgICB9LFxuICAgIGNyZWF0ZWQ6IGZ1bmN0aW9uICgpIHtcbiAgICAgICAgaWYgKHRoaXMuY29tbWVudHMubGVuZ3RoID09PSAwKSB7XG4gICAgICAgICAgICB0aGlzLmZldGNoQ29tbWVudCgpO1xuICAgICAgICB9XG4gICAgfSxcbiAgICB1cGRhdGVkOiBmdW5jdGlvbiAoKSB7XG4gICAgICAgIHRoaXMuY29tbWVudHMubWFwKChjb21tZW50LCBpbmRleCkgPT4ge1xuICAgICAgICAgICAgdHd0dHIud2lkZ2V0cy5sb2FkKGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdjJyArIGNvbW1lbnQuY29tbWVudF9pZCkpO1xuICAgICAgICB9KTtcbiAgICB9LFxuICAgIG1ldGhvZHM6IHtcbiAgICAgICAgZmV0Y2hDb21tZW50OiBmdW5jdGlvbiAoKSB7XG4gICAgICAgICAgICBpZiAodGhpcy5sb2FkaW5nKSB7XG4gICAgICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICAgICAgfVxuICAgICAgICAgICAgdGhpcy5sb2FkaW5nID0gdHJ1ZTtcblxuICAgICAgICAgICAgZmV0Y2goZmV0Y2hfcGF0aCArIHRoaXMubGFzdF9pZCwge1xuICAgICAgICAgICAgICAgIG1ldGhvZDogJ0dFVCcsXG4gICAgICAgICAgICAgICAgaGVhZGVyczogeydYLVJlcXVlc3RlZC1XaXRoJzogJ1hNTEh0dHBSZXF1ZXN0J31cbiAgICAgICAgICAgIH0pLnRoZW4oKHJlc3BvbnNlKSA9PiB7XG4gICAgICAgICAgICAgICAgaWYgKCFyZXNwb25zZS5vaykge1xuICAgICAgICAgICAgICAgICAgICB0aHJvdyBFcnJvcihyZXNwb25zZS5zdGF0dXNUZXh0KTtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgcmV0dXJuIHJlc3BvbnNlLmpzb24oKTtcbiAgICAgICAgICAgIH0pLnRoZW4oKHJlc3BvbnNlKSA9PiB7XG4gICAgICAgICAgICAgICAgdGhpcy5lcnJvcl9tc2cgPSAnJztcblxuICAgICAgICAgICAgICAgIGNvbnN0IG5ld19jb21tZW50ID0gT2JqZWN0LmtleXMocmVzcG9uc2UpLm1hcCgoa2V5LCBpbmRleCkgPT4ge1xuICAgICAgICAgICAgICAgICAgICByZXR1cm4gbmV3IENvbW1lbnQoa2V5LCByZXNwb25zZVtrZXldKTtcbiAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgICAgICB0aGlzLmNvbW1lbnRzLnB1c2goLi4ubmV3X2NvbW1lbnQpO1xuICAgICAgICAgICAgICAgIHRoaXMubGFzdF9pZCA9IHRoaXMuY29tbWVudHNbdGhpcy5jb21tZW50cy5sZW5ndGggLSAxXS5jb21tZW50X2lkO1xuICAgICAgICAgICAgfSkuY2F0Y2goKGVycm9yKSA9PiB7XG4gICAgICAgICAgICAgICAgdGhpcy5lcnJvcl9tc2cgPSAn44Kz44Oh44Oz44OI44KS5Y+W5b6X44Gn44GN44G+44Gb44KT44Gn44GX44Gf44CCJztcbiAgICAgICAgICAgIH0pLmZpbmFsbHkoKCkgPT4ge1xuICAgICAgICAgICAgICAgIHRoaXMubG9hZGluZyA9IGZhbHNlO1xuICAgICAgICAgICAgfSk7XG4gICAgICAgIH0sXG4gICAgICAgIGlzT3duZWQ6IGZ1bmN0aW9uIChpZCkge1xuICAgICAgICAgICAgaWYgKHVzZXJfaWQgPT09IFwiMFwiKSB7XG4gICAgICAgICAgICAgICAgcmV0dXJuIGZhbHNlO1xuICAgICAgICAgICAgfVxuICAgICAgICAgICAgcmV0dXJuIGlkID09PSB1c2VyX2lkO1xuICAgICAgICB9LFxuICAgICAgICBlZGl0aW5nOiBmdW5jdGlvbiAoY29tbWVudCkge1xuICAgICAgICAgICAgcmV0dXJuIGNvbW1lbnQuZWRpdDtcbiAgICAgICAgfSxcbiAgICAgICAgc2F2aW5nOiBmdW5jdGlvbiAoY29tbWVudCkge1xuICAgICAgICAgICAgcmV0dXJuIGNvbW1lbnQuc2F2aW5nO1xuICAgICAgICB9LFxuICAgICAgICBzb3VkYW5lOiBmdW5jdGlvbiAoY29tbWVudCkge1xuICAgICAgICAgICAgcmV0dXJuICfjgZ3jgYbjgaDjga0gw5cnICsgY29tbWVudC5jb3VudDtcbiAgICAgICAgfSxcbiAgICAgICAgaGFzX2Vycm9yOiBmdW5jdGlvbiAoY29tbWVudCkge1xuICAgICAgICAgICAgcmV0dXJuIGNvbW1lbnQuZXJyb3JfbXNnICE9PSAnJztcbiAgICAgICAgfSxcbiAgICAgICAgaGFzX21lc3NhZ2U6IGZ1bmN0aW9uIChjb21tZW50KSB7XG4gICAgICAgICAgICByZXR1cm4gY29tbWVudC5zdWNjZXNzX21zZyAhPT0gJyc7XG4gICAgICAgIH0sXG4gICAgICAgIGNvbW1lbnRfY29tcHV0ZWQ6IGJ1aWxkX2NvbW1lbnQsXG4gICAgICAgIC8vIGVkaXRcbiAgICAgICAgZWRpdFN0YXJ0OiBmdW5jdGlvbiAoY29tbWVudCkge1xuICAgICAgICAgICAgY29tbWVudC5lZGl0ID0gdHJ1ZTtcbiAgICAgICAgICAgIGNvbW1lbnQucHJlX2NvbW1lbnQgPSBjb21tZW50LmNvbW1lbnQ7XG4gICAgICAgICAgICBjb21tZW50LnN1Y2Nlc3NfbXNnID0gJyc7XG4gICAgICAgIH0sXG4gICAgICAgIGVkaXRDYW5jZWw6IGZ1bmN0aW9uIChjb21tZW50LCBldmVudCkge1xuICAgICAgICAgICAgZXZlbnQucHJldmVudERlZmF1bHQoKTtcbiAgICAgICAgICAgIGNvbW1lbnQuZWRpdCA9IGZhbHNlO1xuICAgICAgICAgICAgY29tbWVudC5jb21tZW50ID0gY29tbWVudC5wcmVfY29tbWVudDtcbiAgICAgICAgICAgIGNvbW1lbnQuZXJyb3JfbXNnID0gJyc7XG4gICAgICAgIH0sXG4gICAgICAgIGVkaXRFbmQ6IGZ1bmN0aW9uIChjb21tZW50LCBldmVudCkge1xuICAgICAgICAgICAgZXZlbnQucHJldmVudERlZmF1bHQoKTtcbiAgICAgICAgICAgIGNvbW1lbnQuc2F2aW5nID0gdHJ1ZTtcbiAgICAgICAgICAgIGNvbW1lbnQuZXJyb3JfbXNnID0gJyc7XG5cbiAgICAgICAgICAgIGNvbnN0IGVkaXRfZG9tID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoZXZlbnQudGFyZ2V0LmlkKTtcbiAgICAgICAgICAgIGlmICghZWRpdF9kb20pIHtcbiAgICAgICAgICAgICAgICByZXR1cm47XG4gICAgICAgICAgICB9XG4gICAgICAgICAgICBjb25zdCBlZGl0X2Zvcm0gPSBlZGl0X2RvbS5wYXJlbnRFbGVtZW50O1xuICAgICAgICAgICAgaWYgKCEoZWRpdF9mb3JtIGluc3RhbmNlb2YgSFRNTEZvcm1FbGVtZW50KSkge1xuICAgICAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgICAgIH1cblxuICAgICAgICAgICAgZmV0Y2godXBkYXRlX3BhdGgsIHtcbiAgICAgICAgICAgICAgICBtZXRob2Q6ICdQT1NUJyxcbiAgICAgICAgICAgICAgICBoZWFkZXJzOiB7J1gtUmVxdWVzdGVkLVdpdGgnOiAnWE1MSHR0cFJlcXVlc3QnfSxcbiAgICAgICAgICAgICAgICBib2R5OiBuZXcgRm9ybURhdGEoZWRpdF9mb3JtKVxuICAgICAgICAgICAgfSlcbiAgICAgICAgICAgICAgICAudGhlbigocmVzcG9uc2UpID0+IHtcbiAgICAgICAgICAgICAgICAgICAgaWYgKCFyZXNwb25zZS5vaykge1xuICAgICAgICAgICAgICAgICAgICAgICAgdGhyb3cgRXJyb3IocmVzcG9uc2Uuc3RhdHVzVGV4dCk7XG4gICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAgICAgY29tbWVudC5lcnJvcl9tc2cgPSAnJztcbiAgICAgICAgICAgICAgICAgICAgY29tbWVudC5zdWNjZXNzX21zZyA9ICfjgrPjg6Hjg7Pjg4jjgpLkv53lrZjjgZfjgb7jgZfjgZ/jgIInO1xuICAgICAgICAgICAgICAgICAgICBjb21tZW50LmVkaXQgPSBmYWxzZTtcbiAgICAgICAgICAgICAgICB9KVxuICAgICAgICAgICAgICAgIC5jYXRjaCgoZSkgPT4ge1xuICAgICAgICAgICAgICAgICAgICBjb21tZW50LmVkaXQgPSB0cnVlO1xuICAgICAgICAgICAgICAgICAgICBjb21tZW50LmVycm9yX21zZyA9ICfkv53lrZjjgafjgY3jgb7jgZvjgpPjgafjgZfjgZ/jgIInO1xuICAgICAgICAgICAgICAgIH0pXG4gICAgICAgICAgICAgICAgLmZpbmFsbHkoKGUpID0+IHtcbiAgICAgICAgICAgICAgICAgICAgY29tbWVudC5zYXZpbmcgPSBmYWxzZTtcbiAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgfSxcbiAgICAgICAgLy8tLS0tLS0tLS1cbiAgICAgICAgLy8gbGlrZVxuICAgICAgICBhZGRMaWtlOiBmdW5jdGlvbiAoY29tbWVudCkge1xuICAgICAgICAgICAgaWYgKGNvbW1lbnQudW5zZW50KSB7XG4gICAgICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICAgICAgfVxuICAgICAgICAgICAgY29uc3QgbGlrZV9kb20gPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZChjb21tZW50Lmxpa2VfZm9ybV9pZCk7XG4gICAgICAgICAgICBpZiAoIShsaWtlX2RvbSBpbnN0YW5jZW9mIEhUTUxGb3JtRWxlbWVudCkpIHtcbiAgICAgICAgICAgICAgICByZXR1cm47XG4gICAgICAgICAgICB9XG5cbiAgICAgICAgICAgIGNvbnN0IGZvcm0gPSBuZXcgRm9ybURhdGEobGlrZV9kb20pO1xuICAgICAgICAgICAgdGhpcy5wbHVzMShmb3JtLCBjb21tZW50KTtcbiAgICAgICAgICAgIGNvbW1lbnQuZXJyb3JfbXNnID0gJyc7XG4gICAgICAgIH0sXG4gICAgICAgIHBsdXMxOiBmdW5jdGlvbiAoZm9ybSwgY29tbWVudCkge1xuICAgICAgICAgICAgY29uc3QgX3RoaXMgPSB0aGlzO1xuICAgICAgICAgICAgZmV0Y2goYWRkX2xpa2VfcGF0aCwge1xuICAgICAgICAgICAgICAgIG1ldGhvZDogJ1BPU1QnLFxuICAgICAgICAgICAgICAgIGhlYWRlcnM6IHsnWC1SZXF1ZXN0ZWQtV2l0aCc6ICdYTUxIdHRwUmVxdWVzdCd9LFxuICAgICAgICAgICAgICAgIGJvZHk6IGZvcm1cbiAgICAgICAgICAgIH0pXG4gICAgICAgICAgICAgICAgLnRoZW4oZnVuY3Rpb24gKHJlc3BvbnNlKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICBpZiAoIXJlc3BvbnNlLm9rKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgdGhyb3cgRXJyb3IocmVzcG9uc2Uuc3RhdHVzVGV4dCk7XG4gICAgICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgICAgICAgICBfdGhpcy51cGRhdGVDb3VudChjb21tZW50KTtcbiAgICAgICAgICAgICAgICAgICAgICAgIF90aGlzLmRpc2FibGVkQnV0dG9uKGNvbW1lbnQpO1xuICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgKVxuICAgICAgICAgICAgICAgIC5jYXRjaCgoZSkgPT4ge1xuICAgICAgICAgICAgICAgICAgICBjb21tZW50LmVycm9yX21zZyA9ICfjgIzjgZ3jgYbjgaDjga3jgI3jgafjgY3jgb7jgZvjgpPjgafjgZfjgZ/jgIInO1xuICAgICAgICAgICAgICAgIH0pO1xuICAgICAgICB9LFxuICAgICAgICB1cGRhdGVDb3VudDogZnVuY3Rpb24gKGNvbW1lbnQpIHtcbiAgICAgICAgICAgIGNvbW1lbnQuY291bnQgPSAocGFyc2VJbnQoY29tbWVudC5jb3VudCkpICsgMTtcbiAgICAgICAgfSxcbiAgICAgICAgZGlzYWJsZWRCdXR0b246IGZ1bmN0aW9uIChjb21tZW50KSB7XG4gICAgICAgICAgICBjb21tZW50LnVuc2VudCA9IHRydWU7XG4gICAgICAgIH0sXG4gICAgICAgIC8vLS0tLS0tLS0tXG4gICAgICAgIC8vIGRlbGV0ZVxuICAgICAgICBkZWxldGVDb25maXJtOiBmdW5jdGlvbiAoZXZlbnQpIHtcbiAgICAgICAgICAgIGNvbnN0IGFuc3dlciA9IGNvbmZpcm0oJ+OBk+OBruOCs+ODoeODs+ODiOOCkuWJiumZpOOBl+OBvuOBmeOBi++8nycpO1xuICAgICAgICAgICAgaWYgKGFuc3dlciA9PT0gZmFsc2UpIHtcbiAgICAgICAgICAgICAgICBldmVudC5wcmV2ZW50RGVmYXVsdCgpO1xuICAgICAgICAgICAgfVxuICAgICAgICB9LFxuICAgICAgICAvLy0tLS0tLS0tLVxuICAgICAgICAvLyBzY3JvbGxcbiAgICAgICAgc2Nyb2xsaW5nOiBmdW5jdGlvbiAoZXZlbnQpIHtcbiAgICAgICAgICAgIGNvbnN0IGJvZHkgPSBkb2N1bWVudC5ib2R5O1xuICAgICAgICAgICAgY29uc3QgaHRtbCA9IGRvY3VtZW50LmRvY3VtZW50RWxlbWVudDtcbiAgICAgICAgICAgIGlmICghYm9keSB8fCAhaHRtbCkge1xuICAgICAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgICAgIH1cbiAgICAgICAgICAgIGNvbnN0IHNjcm9sbFRvcCA9IGJvZHkuc2Nyb2xsVG9wIHx8IGh0bWwuc2Nyb2xsVG9wO1xuICAgICAgICAgICAgY29uc3Qgc2Nyb2xsQm90dG9tID0gaHRtbC5zY3JvbGxIZWlnaHQgLSBzY3JvbGxUb3AgLSBodG1sLmNsaWVudEhlaWdodDtcbiAgICAgICAgICAgIGlmIChzY3JvbGxCb3R0b20gPD0gMCAmJiAhdGhpcy5sb2FkaW5nKSB7XG4gICAgICAgICAgICAgICAgdGhpcy5mZXRjaENvbW1lbnQoKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuICAgIH0sXG4gICAgYmVmb3JlTW91bnQ6IGZ1bmN0aW9uICgpIHtcbiAgICAgICAgd2luZG93LmFkZEV2ZW50TGlzdGVuZXIoJ3Njcm9sbCcsIHRoaXMuc2Nyb2xsaW5nKVxuICAgIH0sXG4gICAgYWZ0ZXJNb3VudDogZnVuY3Rpb24gKCkge1xuICAgICAgICB3aW5kb3cucmVtb3ZlRXZlbnRMaXN0ZW5lcignc2Nyb2xsJywgdGhpcy5zY3JvbGxpbmcpXG4gICAgfSxcbn0pOyIsIi8vIGh0dHBzOi8vZGV2LnR3aXR0ZXIuY29tL3dlYi9qYXZhc2NyaXB0L2xvYWRpbmdcbndpbmRvdy50d3R0ciA9IChmdW5jdGlvbiAoZCwgcywgaWQpIHtcbiAgICB2YXIganMsIGZqcyA9IGQuZ2V0RWxlbWVudHNCeVRhZ05hbWUocylbMF0sXG4gICAgICAgIHQgPSB3aW5kb3cudHd0dHIgfHwge307XG4gICAgaWYgKGQuZ2V0RWxlbWVudEJ5SWQoaWQpKSByZXR1cm4gdDtcbiAgICBqcyA9IGQuY3JlYXRlRWxlbWVudChzKTtcbiAgICBqcy5pZCA9IGlkO1xuICAgIGpzLnNyYyA9IFwiaHR0cHM6Ly9wbGF0Zm9ybS50d2l0dGVyLmNvbS93aWRnZXRzLmpzXCI7XG4gICAgZmpzLnBhcmVudE5vZGUuaW5zZXJ0QmVmb3JlKGpzLCBmanMpO1xuXG4gICAgdC5fZSA9IFtdO1xuICAgIHQucmVhZHkgPSBmdW5jdGlvbiAoZikge1xuICAgICAgICB0Ll9lLnB1c2goZik7XG4gICAgfTtcblxuICAgIHJldHVybiB0O1xufShkb2N1bWVudCwgXCJzY3JpcHRcIiwgXCJ0d2l0dGVyLXdqc1wiKSk7Il19
