!function(){var e={Endpoints:Object.freeze({SubmitComment:"/!/Meerkat/socialize"}),closeOnCancel:!0,replyOpen:null,canceled:null,submit:function(e){},getOpenReplyForm:function(){var e=document.querySelectorAll('form[data-meerkat-form="comment-reply-form"]');return e[e.length-1]}},t={data:{ReplyForm:null,Extend:null},findClosest:function(e,t){var n;["matches","webkitMatchesSelector","mozMatchesSelector","msMatchesSelector","oMatchesSelector"].some(function(e){return"function"==typeof document.body[e]&&(n=e,!0)});for(var a;e;){if(a=e.parentElement,a&&a[n](t))return a;e=a}return null},getReplyForm:function(){var e=document.querySelectorAll('[data-meerkat-form="comment-reply-form"]');0==e.length&&(e=document.querySelectorAll('[data-meerkat-form="comment-form"]')),e.length>0&&(this.data.ReplyForm=e[0].cloneNode(!0))},makeReplyInput:function(e){var t=document.createElement("input");return t.type="hidden",t.value=e,t.name="ids",t},addEventListeners:function(){var e=this,n=document.querySelectorAll('[data-meerkat-form="reply"]');n.forEach(function(n){n.addEventListener("click",function(a){var r=a.target.getAttribute("data-meerkat-reply-to");e.data.ReplyForm.appendChild(e.makeReplyInput(r)),e.data.ReplyForm.addEventListener("submit",e.data.Extend.submit,!1),"undefined"!=typeof t.data.Extend.replyOpen&&null!==t.data.Extend.replyOpen&&t.data.Extend.replyOpen(e.data.ReplyForm),n.parentNode.insertBefore(e.data.ReplyForm,n.nextSibling),e.addCancelReplyListeners(),a.preventDefault()})})},replyHandler:function(e){var n=t.findClosest(e.target,"[data-meerkat-form]");if("undefined"!=typeof n&&null!==n){var a=n.querySelectorAll("[name=ids]")[0].value;"undefined"!=typeof t.data.Extend.canceled&&null!==t.data.Extend.canceled&&t.data.Extend.canceled(a,n),t.data.Extend.closeOnCancel&&(this.removeEventListener("click",t.replyHandler),n.remove())}e.preventDefault()},addCancelReplyListeners:function(){var e=this,t=document.querySelectorAll('[data-meerkat-form="cancel-reply"]');t.forEach(function(t){t.addEventListener("click",e.replyHandler)})},init:function(){this.data.Extend=e,this.getReplyForm(),this.addEventListeners(),window.MeerkatReply=this.data.Extend}};t.init()}();