!function(){return function t(e,n,r){function o(s,c){if(!n[s]){if(!e[s]){var u="function"==typeof require&&require;if(!c&&u)return u(s,!0);if(i)return i(s,!0);var a=new Error("Cannot find module '"+s+"'");throw a.code="MODULE_NOT_FOUND",a}var m=n[s]={exports:{}};e[s][0].call(m.exports,function(t){return o(e[s][1][t]||t)},m,m.exports,t,e,n,r)}return n[s].exports}for(var i="function"==typeof require&&require,s=0;s<r.length;s++)o(r[s]);return o}}()({1:[function(t,e,n){"use strict";var r='<blockquote class="twitter-tweet"><a href="$url"></a></blockquote>',o='<a href="{url}" target="_new">{url}</a>',i=/^https?:\/\/twitter.com\/(.*)\/(status|statuses)\/(\d+)(\?s=\d+)*$/,s=/(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]\.[^\s]{2,})/,c=function t(e,n){!function(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}(this,t),this._comment=n,this.id=e,this.user_id=n.user_id,this.comment_id=n.comment_id,this.comment=n.comment,this.pre_comment=n.comment,this.like_form_id="like-"+n.comment_id,this.count=n.like_count,this.edit=!1,this.unsent=!1,this.saving=!1,this.success_msg="",this.error_msg=""};function u(t){var e=t.split("\n");return(e=e.map(function(t){if(i.test(t))return r.replace("$url",t);if(s.test(t)){var e=t.match(s);if(e&&e.length>0)return t.replace(e[0],o.replace(/{url}/g,e[0]))}return t})).join("<br/>")}var a=document.getElementById("top_comment");a&&a.dataset&&(a.innerHTML=u(a.dataset.comment));var m=Object.keys(comments||[]).map(function(t,e){return new c(t,comments[t])}),d=m[m.length-1]&&m[m.length-1].comment_id||Number.MAX_SAFE_INTEGER;new Vue({delimiters:["${","}"],el:"#js-comments",data:{comments:m,error_msg:"",last_id:d,loading:!1},created:function(){0===this.comments.length&&this.fetchComment()},updated:function(){this.comments.map(function(t,e){twttr.widgets.load(document.getElementById("c"+t.comment_id))})},methods:{fetchComment:function(){var t=this;this.loading||(this.loading=!0,fetch(fetch_path+this.last_id,{method:"GET",headers:{"X-Requested-With":"XMLHttpRequest"}}).then(function(t){if(!t.ok)throw Error(t.statusText);return t.json()}).then(function(e){var n;t.error_msg="";var r=Object.keys(e).map(function(t,n){return new c(t,e[t])});(n=t.comments).push.apply(n,function(t){if(Array.isArray(t)){for(var e=0,n=Array(t.length);e<t.length;e++)n[e]=t[e];return n}return Array.from(t)}(r)),t.last_id=t.comments[t.comments.length-1].comment_id}).catch(function(e){t.error_msg="コメントを取得できませんでした。"}).finally(function(){t.loading=!1}))},isOwned:function(t){return"0"!==user_id&&t===user_id},editing:function(t){return t.edit},saving:function(t){return t.saving},soudane:function(t){return"そうだね ×"+t.count},has_error:function(t){return""!==t.error_msg},has_message:function(t){return""!==t.success_msg},comment_computed:u,editStart:function(t){t.edit=!0,t.pre_comment=t.comment,t.success_msg=""},editCancel:function(t,e){e.preventDefault(),t.edit=!1,t.comment=t.pre_comment,t.error_msg=""},editEnd:function(t,e){e.preventDefault(),t.saving=!0,t.error_msg="";var n=document.getElementById(e.target.id);if(n){var r=n.parentElement;r instanceof HTMLFormElement&&fetch(update_path,{method:"POST",headers:{"X-Requested-With":"XMLHttpRequest"},body:new FormData(r)}).then(function(e){if(!e.ok)throw Error(e.statusText);t.error_msg="",t.success_msg="コメントを保存しました。",t.edit=!1}).catch(function(e){t.edit=!0,t.error_msg="保存できませんでした。"}).finally(function(e){t.saving=!1})}},addLike:function(t){if(!t.unsent){var e=document.getElementById(t.like_form_id);if(e instanceof HTMLFormElement){var n=new FormData(e);this.plus1(n,t),t.error_msg=""}}},plus1:function(t,e){var n=this;fetch(add_like_path,{method:"POST",headers:{"X-Requested-With":"XMLHttpRequest"},body:t}).then(function(t){if(!t.ok)throw Error(t.statusText);n.updateCount(e),n.disabledButton(e)}).catch(function(t){e.error_msg="「そうだね」できませんでした。"})},updateCount:function(t){t.count=parseInt(t.count)+1},disabledButton:function(t){t.unsent=!0},deleteConfirm:function(t){!1===confirm("このコメントを削除しますか？")&&t.preventDefault()},scrolling:function(t){var e=document.body,n=document.documentElement;if(e&&n){var r=e.scrollTop||n.scrollTop;n.scrollHeight-r-n.clientHeight<=0&&!this.loading&&this.fetchComment()}}},beforeMount:function(){window.addEventListener("scroll",this.scrolling)},afterMount:function(){window.removeEventListener("scroll",this.scrolling)}})},{}],2:[function(t,e,n){"use strict";var r,o,i,s,c,u;window.twttr=(r=document,o="script",i="twitter-wjs",c=r.getElementsByTagName(o)[0],u=window.twttr||{},r.getElementById(i)?u:((s=r.createElement(o)).id=i,s.src="https://platform.twitter.com/widgets.js",c.parentNode.insertBefore(s,c),u._e=[],u.ready=function(t){u._e.push(t)},u))},{}]},{},[2,1]);