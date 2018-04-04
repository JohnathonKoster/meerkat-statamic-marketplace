(function () {
    var MeerkatReply = {
        Endpoints: Object.freeze({
            SubmitComment: '/!/Meerkat/socialize'
        }),
        closeOnCancel: true,
        replyOpen: null,
        canceled: null,
        submit: function (event) {
            
        },
        getOpenReplyForm: function () {
            var forms = document.querySelectorAll('form[data-meerkat-form="comment-reply-form"]');

            return forms[forms.length - 1];
        }
    };
    var MeerkatForms = {
        data: {
            ReplyForm: null,
            Extend: null
        },
        findClosest: function (el, selector) {
            var matchesFn;

            ['matches','webkitMatchesSelector','mozMatchesSelector','msMatchesSelector','oMatchesSelector'].some(function(fn) {
                if (typeof document.body[fn] == 'function') {
                    matchesFn = fn;
                    return true;
                }
                return false;
            })

            var parent;

            while (el) {
                parent = el.parentElement;
                if (parent && parent[matchesFn](selector)) {
                    return parent;
                }
                el = parent;
            }

            return null;
        },
        getReplyForm: function () {
            var form = document.querySelectorAll('[data-meerkat-form="comment-reply-form"]');

            if (form.length == 0) {
                form = document.querySelectorAll('[data-meerkat-form="comment-form"]');
            }

            if (form.length > 0) {
                this.data.ReplyForm = form[0].cloneNode(true);
            }

        },
        makeReplyInput: function (replyingTo) {
            var replyInput = document.createElement('input');
            replyInput.type = 'hidden';
            replyInput.value = replyingTo;
            replyInput.name = 'ids';

            return replyInput;
        },
        addEventListeners: function () {
            var _this = this;
             var replyLinks = document.querySelectorAll('[data-meerkat-form="reply"]');

             replyLinks.forEach(function (el) {
                el.addEventListener('click', function (event) {
                    var replyingTo = event.target.getAttribute('data-meerkat-reply-to');

                    _this.data.ReplyForm.appendChild(_this.makeReplyInput(replyingTo));
                    _this.data.ReplyForm.addEventListener('submit', _this.data.Extend.submit, false);

                    if (typeof MeerkatForms.data.Extend.replyOpen !== 'undefined' && MeerkatForms.data.Extend.replyOpen !== null) {
                        MeerkatForms.data.Extend.replyOpen(_this.data.ReplyForm);
                    }
                    
                    el.parentNode.insertBefore(_this.data.ReplyForm, el.nextSibling);
                    _this.addCancelReplyListeners();
                    event.preventDefault();
                });
             });
        },
        replyHandler: function (event) {
            var meerkatForm = MeerkatForms.findClosest(event.target, '[data-meerkat-form]');

            if (typeof meerkatForm !== 'undefined' && meerkatForm !== null) {
                
                var replyingTo = meerkatForm.querySelectorAll('[name=ids]')[0].value;

                if (typeof MeerkatForms.data.Extend.canceled !== 'undefined' && MeerkatForms.data.Extend.canceled !== null) {
                    MeerkatForms.data.Extend.canceled(replyingTo, meerkatForm);
                }

                if (MeerkatForms.data.Extend.closeOnCancel) {
                    this.removeEventListener('click', MeerkatForms.replyHandler);
                    meerkatForm.remove();
                }
            }

            event.preventDefault();
        },
        addCancelReplyListeners: function () {
            var _this = this;
            var cancelLinks = document.querySelectorAll('[data-meerkat-form="cancel-reply"]');

            cancelLinks.forEach(function (el) {
                el.addEventListener('click', _this.replyHandler);
            });
        },
        init: function () {
            this.data.Extend = MeerkatReply;
            this.getReplyForm();
            this.addEventListeners();
            window.MeerkatReply = this.data.Extend;
        }
    };

    MeerkatForms.init();
})();