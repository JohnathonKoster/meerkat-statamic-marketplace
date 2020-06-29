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
            Extend: null,
            IsGoogleRecaptchaInUse: false,
            CaptchaElementId: null,
            GoogleRecaptchaInstance: null,
            GoogleRecaptchaTheme: null,
            GoogleRecaptchaSiteKey: null
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
        generateId: function () {
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
                return v.toString(16);
            });
        },
        getReplyForm: function () {
            var form = document.querySelectorAll('[data-meerkat-form="comment-reply-form"]');

            if (form.length == 0) {
                form = document.querySelectorAll('[data-meerkat-form="comment-form"]');
            }


            if (form.length > 0) {
                var meerkatReplyForm = form[0].cloneNode(true);

                if (meerkatReplyForm.innerHTML.indexOf('g-recaptcha') > -1) {
                    if (typeof window['grecaptcha'] !== 'undefined') {
                        this.data.IsGoogleRecaptchaInUse = true;

                        for (var i = 0; i < meerkatReplyForm.childNodes.length; i += 1) {
                            if (typeof meerkatReplyForm.childNodes[i].className !== 'undefined') {
                                if (meerkatReplyForm.childNodes[i].className.match("\s*g-recaptcha\s*")) {
                                    this.data.CaptchaElementId = 'meerkat_c-' + this.generateId();
                                    meerkatReplyForm.childNodes[i].setAttribute('id', this.data.CaptchaElementId);

                                    if (typeof meerkatReplyForm.childNodes[i].dataset !== 'undefined') {
                                        var captchaDataSet = meerkatReplyForm.childNodes[i].dataset;

                                        if (typeof captchaDataSet.sitekey !== 'undefined') {
                                            this.data.GoogleRecaptchaSiteKey = captchaDataSet.sitekey;
                                        }

                                        if (typeof captchaDataSet.theme !== 'undefined') {
                                            this.data.GoogleRecaptchaTheme = captchaDataSet.theme;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                form = meerkatReplyForm;
            }

            return form;
        },
        resetCaptchaId: function () {
            if (this.data.ReplyForm !== null) {
                for (var i = 0; i < this.data.ReplyForm.childNodes.length; i += 1) {
                    if (typeof this.data.ReplyForm.childNodes[i].className !== 'undefined') {
                        if (this.data.ReplyForm.childNodes[i].className.match("\s*g-recaptcha\s*")) {
                            this.data.CaptchaElementId = 'meerkat_c-' + this.generateId();
                            this.data.ReplyForm.childNodes[i].setAttribute('id', this.data.CaptchaElementId);

                            if (typeof this.data.ReplyForm.childNodes[i].dataset !== 'undefined') {
                                var captchaDataSet = this.data.ReplyForm.childNodes[i].dataset;

                                if (typeof captchaDataSet.sitekey !== 'undefined') {
                                    this.data.GoogleRecaptchaSiteKey = captchaDataSet.sitekey;
                                }

                                if (typeof captchaDataSet.theme !== 'undefined') {
                                    this.data.GoogleRecaptchaTheme = captchaDataSet.theme;
                                }
                            }
                        }
                    }
                }
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

                    if (_this.data.ReplyForm !== null) {
                        _this.data.ReplyForm.parentNode.removeChild(_this.data.ReplyForm);
                    }

                    _this.data.ReplyForm = _this.getReplyForm();

                    var replyingTo = event.target.getAttribute('data-meerkat-reply-to');

                    _this.data.ReplyForm.appendChild(_this.makeReplyInput(replyingTo));
                    _this.data.ReplyForm.addEventListener('submit', _this.data.Extend.submit, false);

                    if (typeof MeerkatForms.data.Extend.replyOpen !== 'undefined' && MeerkatForms.data.Extend.replyOpen !== null) {
                        MeerkatForms.data.Extend.replyOpen(_this.data.ReplyForm);
                    }

                    el.parentNode.insertBefore(_this.data.ReplyForm, el.nextSibling);

                    if (_this.data.IsGoogleRecaptchaInUse && _this.data.CaptchaElementId !== null) {
                        if (_this.data.GoogleRecaptchaTheme !== null && _this.data.GoogleRecaptchaSiteKey !== null) {
                            window.setTimeout(function () {
                                var captchaElement = window.document.getElementById(_this.data.CaptchaElementId);
                                captchaElement.innerHTML = '';

                                try {
                                    _this.data.GoogleRecaptchaInstance = window.grecaptcha.render(_this.data.CaptchaElementId, {
                                        'sitekey': _this.data.GoogleRecaptchaSiteKey,
                                        'theme': _this.data.GoogleRecaptchaTheme
                                    });
                                } catch (err) {
                                }
                            }, 250);
                        }
                    }

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