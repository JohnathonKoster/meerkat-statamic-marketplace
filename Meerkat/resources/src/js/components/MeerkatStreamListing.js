window.MeerkatStreamListing = MeerkatStreamListing = Vue.component('meerkat-stream-listing', {

    props: [
        'get',
        'delete',
        'spam',
        'notspam',
        'approve',
        'unapprove',
        'update',
        'create',
        'checkspam',
        'perpage',
        'ifilter',
        'hidemanagement',
        'autoload',
        'getcounts'
    ],

    data: function () {
        return {
            cpNav: null,
            loading: true,
            checkingSpam: false,
            items: [],
            streamFilter: null,
            columns: [],
            search: null,
            metrics: [],
            reordering: false,
            filter: 'all',
            hideManagement: this.hidemanagement,
            ajax: {
                get: this.get,
                delete: this.delete,
                spam: this.spam,
                notspam: this.notspam,
                approve: this.approve,
                unapprove: this.unapprove,
                update: this.update,
                create: this.create,
                checkSpam: this.checkspam,
                getcounts: this.getcounts
            },
            pagination: {
                nextPage: null,
                prevPage: null,
                from: null,
                to: null,
                lastPage: null,
                page: null
            },
            tableOptions: {
                checkboxes: true,
                sort: 'datestamp',
                sortOrder: 'desc',
                showBulkOptions: true,
                paginate: true,
                currentPage: 1,
                perPage: this.perpage,
                partials: {
                    cell: Meerkat.getMeerkatCellTemplate()
                }
            },
            instanceChanges: {
                commentEdits: [],
                commentRemovals: [],
                commentReplies: [],
                commentMarkedAsSpam: [],
                commentMarkedAsNotSpam: [],
                commentApprovals: [],
                commentUnApprovals: []
            },
            bulkAction: 'delete',
            applyingBulkActions: false,
            avatarDriver: Meerkat.config.avatar_driver,
            passMobileOverride: false,
            loadStreamFor: null,
            intendedComment: null
        }
    },

    computed: {

        isPaginated: function() {
            return this.tableOptions.paginate;
        },

        pages: function() {
            var maxButtons = 5;
            var pages = [];
            var halfWay = Math.ceil(maxButtons / 2);
            var position;

            if (this.pagination.page <= halfWay) {
                position = 'start';
            } else if (this.pagination.lastPage - halfWay < this.pagination.page) {
                position = 'end';
            } else {
                position = 'middle';
            }

            var ellipsesNeeded = maxButtons < this.pagination.lastPage;
            var i = 1;

            while (i <= this.pagination.lastPage && i <= maxButtons) {
                var openingEllipsesNeeded = (i === 2 && (position === 'middle' || position === 'end'));
                var closingEllipsesNeeded = (i === maxButtons - 1 && (position === 'middle' || position === 'start'));

                if (openingEllipsesNeeded) {
                    pages.push({name: '...', value: '...', page: null});
                } else if (closingEllipsesNeeded) {
                    pages.push({name: '...', value: '...', page: null});
                } else {
                    var pageNumber = i;

                    if (maxButtons === 1) {
                        pageNumber = this.pagination.page;
                    } else if (i === maxButtons) {
                        pageNumber = this.pagination.lastPage;
                    } else if (i === 1) {
                        pageNumber = i;
                    } else if (maxButtons < this.pagination.lastPage) {
                        if (this.pagination.lastPage - halfWay < this.pagination.page) {
                            pageNumber = this.pagination.lastPage - maxButtons + i;
                        } else if (halfWay < this.pagination.page) {
                            pageNumber = this.pagination.page - halfWay + i;
                        } else {
                            pageNumber = i;
                        }
                    } else {
                        pageNumber = i;
                    }

                    pages.push({name: pageNumber, value: pageNumber, page: pageNumber, active: pageNumber === this.pagination.page});
                }
                i++;
            }

            return pages;
        },

        hasItems: function () {
            return !this.loading && this.items && this.items.length > 0;
        },

        noItems: function () {
            return !this.loading && this.items && !this.items.length;
        },

        checkedItems: function () {
            return this.items.filter(function (item) {
                return item.checked;
            }).map(function (item) {
                return item.id;
            });
        },

        allItemsChecked: function () {
            return this.items.length === this.checkedItems.length;
        },

        commentCount: function () {
            return this.metrics.all;
        }
    },

    ready: function () {
        this.cpNav = $('a[href$="meerkat?source=cp-nav"]');
        if (typeof window.Statamic.Publish !== 'undefined') {
            if (typeof window.Statamic.Publish.contentData !== 'undefined') {
                if (typeof window.Statamic.Publish.contentData.id !== 'undefined') {
                    this.streamFilter = window.Statamic.Publish.contentData.id;
                }
            }
        }

        if (this.ifilter != null) {
            if (this.ifilter == 'all') {
                this.filter = 'all';
                if (this.autoload) {
                    this.filterItems('all');
                }
            } else if (this.ifilter == 'pending') {
                this.filter = 'pending';
                if (this.autoload) {
                    this.filterItems('pending');
                }
            } else if (this.ifilter == 'spam') {
                this.filter = 'spam';
                if (this.autoload) {
                    this.filterItems('spam');
                }
            } else if (this.ifilter == 'approved') {
                this.filter = 'approved';
                if (this.autoload) {
                    this.filterItems('approved');                    
                }
            } else {
                this.filter = 'all';
                if (this.autoload) {
                    this.filterItems('all');                    
                }
            }
        } else {
            this.filter = 'all';
            if (this.autoload) {
                this.filterItems('all');                
            }
        }
        this.setHeaders(this.filter);
        /*if (this.can('super')) {*/
            this.addActionPartial();
        /*}*/

        var _vm = this;
        window.onpopstate = function (event) {
            var query = _vm.getQueryParam(document.location.search);

            if (typeof query.filter !== 'undefined') {
                if (query.filter !== _vm.filter) {
                    var ifilter = query.filter;
                    if (ifilter != null) {
                        if (ifilter == 'all') {
                            _vm.filterItems('all');
                        } else if (ifilter == 'pending') {
                            _vm.filterItems('pending');
                        } else if (ifilter == 'spam') {
                            _vm.filterItems('spam');
                        } else if (ifilter == 'approved') {
                            _vm.filterItems('approved');
                        } else {
                            _vm.filterItems('all');
                        }
                    } else {
                        _vm.filterItems('all');
                    }
                }
            }
        }

    },

    components: {
        'dossier-table': DossierTable
    },

    methods: {
        parentStateNeedsRefresh: function () {
            var changeCount = 0;
            changeCount += this.instanceChanges.commentEdits.length;
            changeCount += this.instanceChanges.commentRemovals.length;
            changeCount += this.instanceChanges.commentReplies.length;
            changeCount += this.instanceChanges.commentMarkedAsNotSpam.length;
            changeCount += this.instanceChanges.commentMarkedAsSpam.length;
            changeCount += this.instanceChanges.commentApprovals.length;
            changeCount += this.instanceChanges.commentUnApprovals.length;
            return changeCount > 0;
        },
        openConversation: function (id) {
            this.$parent.meerkat_ConversationView.open(id);
        },
        streamFor: function (id) {
            this.loadStreamFor = id;
        },
        setIntendedComment: function (id) {
            this.intendedComment = id;
            this.$children[0].setIntended(id);
        },
        setSortingOrder: function(col, order) {
            this.tableOptions.sortOrder = order;
            this.$children[0].sortBy(col);
        },
        overrideMobile: function () {
            this.passMobileOverride = true;
        },
        checkForSpam: function() {
            this.checkingSpam = true;
            var self = this;

            this.$http.post(this.ajax.checkSpam, {}, function(data, status, request) {
                self.checkingSpam = false;

                // Refresh the items.
                self.getItems();
                self.refreshCounts();
            });

        },
        refreshCounts: function () {
            var self = this;
          this.$http.get(this.ajax.getcounts, {}, function (data) {

              if (typeof data !== 'object') {
                  return;
              }

              self.metrics.all = data.counts.all;
              self.metrics.approved = data.counts.approved;
              self.metrics.pending = data.counts.pending;
              self.metrics.spam = data.counts.spam;

              if (data.counts.pending <= 0) {
                  self.cpNav.find('.badge').remove();
              } else {
                  if (data.counts.pending > 0) {
                      if (self.cpNav.find('.badge').length > 0) {
                          self.cpNav.find('.badge').text(data.counts.pending);
                      } else {
                          var badge = $('<span class="badge bg-red">' + data.counts.pending + '</span>');
                          self.cpNav.append(badge);
                      }
                  }
              }
          });
        },
        refreshView: function(callback) {

            if (typeof this.filter === 'undefined' || this.filter == null || this.filter.length == 0) {
                this.filter = 'all';
            }

            this.getItems(callback);
        },
        setHeaders: function (filter) {
            if (typeof filter !== 'undefined' && filter !== null) {
                if (filter == 'approved') {
                    $('[data-meerkat-ui="comments-header"]').text(
                        translate('addons.Meerkat::comments.metric_approved') + ' ' +
                        translate('addons.Meerkat::comments.comments')
                    );
                    $('[data-meerkat-ui="comments-body-header"]').text(
                        translate('addons.Meerkat::comments.comments_approved_possessive')
                    );
                    $('[data-meerkat-ui="comments-body-subheader"]').text(
                        translate('addons.Meerkat::comments.no_approved_comments')
                    );
                } else if (filter == 'pending') {
                    $('[data-meerkat-ui="comments-header"]').text(
                        translate('addons.Meerkat::comments.metric_pending') + ' ' +
                        translate('addons.Meerkat::comments.comments')
                    );
                    $('[data-meerkat-ui="comments-body-header"]').text(
                        translate('addons.Meerkat::comments.comments_pending_possessive')
                    );
                    $('[data-meerkat-ui="comments-body-subheader"]').text(
                        translate('addons.Meerkat::comments.no_pending_comments')
                    );
                } else if (filter == 'spam') {
                    $('[data-meerkat-ui="comments-header"]').text(
                        translate('addons.Meerkat::comments.metric_spam') + ' ' +
                        translate('addons.Meerkat::comments.comments')
                    );
                    $('[data-meerkat-ui="comments-body-header"]').text(
                        translate('addons.Meerkat::comments.comments_spam_possessive')
                    );
                    $('[data-meerkat-ui="comments-body-subheader"]').text(
                        translate('addons.Meerkat::comments.no_spam_comments')
                    );
                } else {
                    $('[data-meerkat-ui="comments-header"]').text(
                        translate('addons.Meerkat::comments.comments')
                    );
                    $('[data-meerkat-ui="comments-body-header"]').text(
                        translate('addons.Meerkat::comments.comments_possessive')
                    );
                    $('[data-meerkat-ui="comments-body-subheader"]').text(
                        translate('addons.Meerkat::comments.no_comments')
                    );
                }
            }
        },
        getItems: function (callback) {
            this.loading = true;

            var params = { filter: this.filter };

            if (this.tableOptions.paginate) {
                params.paginate = true;
                params.perPage = this.tableOptions.perPage;
                params.page = this.tableOptions.currentPage;
            }

            if (this.streamFilter !== null) {
                params.stream = this.streamFilter;
            }

            if (this.loadStreamFor !== null) {
                params.stream = null;
                params.streamFor = this.loadStreamFor;
            }

            var _vm = this;
            this.$http.get(this.ajax.get, params, function (data, status, request) {
                var comments = [];

                /* Add some state properties. */
                for (let i = 0; i < data.items.data.length; i += 1) {
                    var comment = data.items.data[i];
                    comment.is_approving = false;
                    comment.is_unapproving = false;
                    comment.is_markingspam = false;
                    comment.is_markingnotspam = false;
                    comment.is_deleting = false;
                    comment.is_taking_action = false;

                    comments.push(comment);
                }

                if (_vm.tableOptions.paginate == true) {
                    _vm.items = comments;
                    _vm.pagination.from = data.items.from;
                    _vm.pagination.to = data.items.to;
                    _vm.pagination.prevPage = data.items.prev_page_url;
                    _vm.pagination.nextPage = data.items.next_page_url;
                    _vm.pagination.lastPage = data.items.last_page;
                    _vm.pagination.page = _vm.tableOptions.currentPage;
                } else {
                    _vm.items = comments;
                }
                
                if (typeof callback !== 'undefined' && callback != null) {
                    callback(_vm.items);
                }

                _vm.columns = data.columns;
                _vm.metrics = data.statistics;
                _vm.loading = false;
                _vm.setHeaders(_vm.filter);
                window.setTimeout(function () {
                    _vm.setHeaders(_vm.filter);
                }, 25);
            }).error(function (response) {
                alert('There was a problem retrieving data. Check your logs for more details.');
            });
        },

        previousPage: function() {
            if (this.pagination.prevPage !== null) {
                this.tableOptions.currentPage--;
                this.getItems();
            }
        },

        nextPage: function() {
            if (this.pagination.nextPage !== null) {
                this.tableOptions.currentPage++;
                this.getItems();
            }
        },

        goToPage: function(page) {
          if (page > 0 && page <= this.pagination.lastPage) {
              this.tableOptions.currentPage = page;
              this.getItems();
          }
        },

        filterItems: function(filter) {
            this.tableOptions.currentPage = 1;
            this.filter = filter;
            this.getItems();

            this.updateURLState(filter);
            this.setHeaders(filter);
            this.refreshCounts();
        },

        updateURLState: function (filter) {
            var currentLocation = window.location.href;
            currentLocation = this.updateQueryString(currentLocation, 'filter', filter);
            if (history.pushState) {
                history.pushState(null, null, currentLocation);
            }
        },
        getQueryParam: function(qs) {
            qs = qs.split('+').join(' ');

            var params = {},
                tokens,
                re = /[?&]?([^=]+)=([^&]*)/g;

            while (tokens = re.exec(qs)) {
                params[decodeURIComponent(tokens[1])] = decodeURIComponent(tokens[2]);
            }

            return params;
        },
        updateQueryString: function (uri, key, value) {
            var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
            var separator = uri.indexOf('?') !== -1 ? "&" : "?";
            if (uri.match(re)) {
                return uri.replace(re, '$1' + key + "=" + value + '$2');
            }
            else {
                return uri + separator + key + "=" + value;
            }
        },

        editComment: function (item) {
            item.editing = true;
        },

        replyToComment: function (item) {
            item.writing_reply = true;
        },
        raiseError: function (messageTitle, fallbackMessage, data) {
            if (typeof data !== 'undefined' && data != null) {
                var dataMessage = '<ul>';
                var allErrors = [];


                for (var errorBucket in data) {
                    if (data.hasOwnProperty(errorBucket)) {
                        if (errorBucket.length > 0) {
                            for (var i = 0; i < data[errorBucket].length; i += 1) {
                                allErrors.push(data[errorBucket][i]);
                            }
                        }
                    }
                }

                var _errorMesssages = allErrors.length,
                    _i = 0;
                
                for (_i; _i < _errorMesssages; _i++) {
                    dataMessage += '<li>' + allErrors[_i] + '</li>';
                }

                dataMessage += '</ul>';

                swal({
                    title: messageTitle,
                    text: dataMessage,
                    type: 'error',
                    html: true
                });

            } else {
                swal(messageTitle, fallbackMessage, 'error');                
            }
        },
        createNewReply: function (item) {
            var self = this;
            var id = item.id;

            item.saving = true;

            self.$http.post(self.ajax.create, {ids: [id], comment: item['new_reply']}, function (data) {
                if (data.success) {
                    self.$parent.flashSuccess = translate('addons.Meerkat::actions.save_success');
                    // Turn off reply editor.
                    item.new_reply = '';
                    item.writing_reply = false;
                    self.items.push(data.submission);
                    self.metrics.all++;
                    self.metrics.pending++;
                    self.instanceChanges.commentReplies.push(data.submission);
                    self.refreshCounts();
                } else {
                    item.saving = false;

                    self.$parent.flashSuccess = false;
                    self.$parent.flashError = translate('addons.Meerkat::actions.save_failure') + data.errorMessage;
                    var title = translate('addons.Meerkat::errors.comments_create_reply');

                    self.raiseError(title, translate('addons.Meerkat::actions.save_failure') + data.errorMessage);
                }

                // Indicate that we are no longer saving the comment, regardless of how it went.
                item.saving = false;
            }).catch(function (e) {
                var title = translate('addons.Meerkat::errors.comments_create_reply');
                var validationError = translate('addons.Meerkat::errors.comments_create_reply_validation');
                var genericError = translate('addons.Meerkat::errors.comments_create_reply_generic');

                item.saving = false;

                if (typeof e.data !== 'undefined' && typeof e.data.errors !== undefined) {
                    this.raiseError(title, validationError, e.data.errors);
                } else {    
                    this.raiseError(title, genericError);
                }
            });
        },

        cancelPostReply: function (item) {
            item.writing_reply = false;
        },

        cancelItemEdit: function (item) {
            item.editing = false;
        },

        saveItemEdits: function (item) {
            var self = this;
            var id = item.id;

            // Indicate that we are saving the comment.
            item.saving = true;

            self.$http.post(self.ajax.update, {ids: [id], comment: item['original_markdown']}, function (data) {
                if (data.success) {
                    self.$parent.flashSuccess = translate('addons.Meerkat::actions.save_success');
                    item.comment = data.parsedContent;
                    // Turn off editing.
                    item.editing = false;
                    self.instanceChanges.commentEdits.push(id);
                    self.refreshCounts();
                } else {
                    self.$parent.flashSuccess = false;
                    self.$parent.flashError = translate('addons.Meerkat::actions.save_failure') + data.errorMessage;
                    var title = translate('addons.Meerkat::errors.comments_save');

                    self.raiseError(title, translate('addons.Meerkat::actions.save_failure') + data.errorMessage);
                }

                // Indicate that we are no longer saving the comment, regardless of how it went.
                item.saving = false;
            }).catch(function (e) {
                var title = translate('addons.Meerkat::errors.comments_save');
                var validationError = translate('addons.Meerkat::errors.comments_save_validation');
                var genericError = translate('addons.Meerkat::errors.comments_save_generic');

                item.saving = false;

                if (typeof e.data !== 'undefined' && typeof e.data.errors !== undefined) {
                    this.raiseError(title, validationError, e.data.errors);
                } else {    
                    this.raiseError(title, genericError);
                }
            });
        },

        removeItemFromList: function (id) {
            var item = _.findWhere(this.items, {id: id});
            var index = _.indexOf(this.items, item);
            this.items.splice(index, 1);
        },

        changeItemToApprove: function (item) {
            item.published = true;
        },

        changeItemToUnApproved: function (item) {
            item.published = false;
        },

        changeItemToIsSpam: function (item) {
            item.spam = true;
        },

        changeItemToNotSpam: function (item) {
            item.spam = false;
        },

        deleteItem: function (item) {
            var self = this;
            var id = item.id;

            swal({
                type: 'warning',
                title: translate('addons.Meerkat::actions.prompt_confirm'),
                text: translate_choice('addons.Meerkat::actions.confirm_delete_comment', 1),
                confirmButtonText: translate('addons.Meerkat::actions.prompt_confirm_action'),
                cancelButtonText: translate('addons.Meerkat::actions.cancel'),
                showCancelButton: true
            }, function () {
                item.is_taking_action = true;
                item.is_deleting = true;

                self.$http.delete(self.ajax.delete, {ids: [id]}, function (data) {
                    _.each(data.removed, function (removedId) {
                        self.removeItemFromList(removedId);
                        self.metrics.all--;
                    });
                    // Just in case ;)
                    self.removeItemFromList(id);
                    self.instanceChanges.commentRemovals.push(id);
                    self.refreshCounts();
                    item.is_taking_action = false;
                    item.is_deleting = false;
                }).catch(function (e) {
                    var title = translate('addons.Meerkat::errors.comments_remove');
                    var genericError = translate('addons.Meerkat::errors.comments_remove_desc');

                    item.is_taking_action = false;
                    item.is_deleting = false;

                    this.raiseError(title, genericError);
                });
            });
        },

        deleteMultiple: function () {
            var self = this;

            swal({
                type: 'warning',
                title: translate('addons.Meerkat::actions.prompt_confirm'),
                text: translate_choice('addons.Meerkat::actions.confirm_delete_comment', 2),
                confirmButtonText: translate('addons.Meerkat::actions.prompt_confirm_action'),
                cancelButtonText: translate('addons.Meerkat::actions.cancel'),
                showCancelButton: true
            }, function () {
                self.applyingBulkActions = true;
                self.$http.delete(self.ajax.delete, {ids: self.checkedItems}, function (data) {
                    if (data.success) {
                        self.$parent.flashSuccess = translate_choice('addons.Meerkat::actions.delete_success', data.removed.length);
                        _.each(data.removed, function (removedId) {
                            self.removeItemFromList(removedId);
                            self.instanceChanges.commentRemovals.push(removedId);
                            self.metrics.all--;
                        });
                    } else {
                        self.$parent.flashSuccess = false;
                        self.$parent.flashError = translate_choice('addons.Meerkat::actions.delete_failed', data.removed.length);
                    }
                    self.applyingBulkActions = false;
                    self.refreshCounts();
                    self.refreshView();
                }).catch(function (e) {
                    var title = translate('addons.Meerkat::errors.comments_remove_plural');
                    var genericError = translate('addons.Meerkat::errors.comments_remove_plural_desc');

                    self.applyBulkActions = false;

                    this.raiseError(title, genericError);
                });
            });
        },

        approveComment: function (item) {
            var self = this;
            self.applyingBulkActions = true;
            item.is_approving = true;
            item.is_taking_action = true;

            self.$http.post(self.ajax.approve, {ids: [item.id]}, function (data) {
                if (data.success === true) {

                    item.is_approving = false;
                    item.is_taking_action = false;

                    self.$parent.flashSuccess = translate_choice('addons.Meerkat::actions.approve_success', data.approved.length);
                    _.each(data.approved, function (approvedId) {
                        var _tItem = _.findWhere(self.items, {id: approvedId});

                        self.changeItemToApprove(_tItem);
                        self.metrics.approved++;
                        self.metrics.pending--;
                        self.instanceChanges.commentApprovals.push(approvedId);
                    });
                    self.refreshCounts();

                } else {
                    self.$parent.flashSuccess = false;
                    self.$parent.flashError = translate_choice('addons.Meerkat::actions.approve_failed', data.approved.length) + data.errorMessage;
                    item.is_approving = false;
                    item.is_taking_action = false;
                }
            }).catch(function (e) {
                var title = translate('addons.Meerkat::errors.comments_approve');
                var genericError = translate('addons.Meerkat::errors.comments_approve_desc');
                item.is_approving = false;
                item.is_taking_action = false;

                this.raiseError(title, genericError);
            });
        },

        approveMultiple: function () {
            var self = this;
            self.applyingBulkActions = true;
            self.$http.post(self.ajax.approve, {ids: self.checkedItems}, function (data) {
                if (data.success) {
                    self.$parent.flashSuccess = translate_choice('addons.Meerkat::actions.approve_success', data.approved.length);
                    _.each(data.approved, function (approvedId) {
                        var _tItem = _.findWhere(self.items, {id: approvedId});

                        self.changeItemToApprove(_tItem);
                        self.metrics.approved++;
                        self.metrics.pending--;
                        self.instanceChanges.commentApprovals.push(approvedId);
                    });
                    self.refreshCounts();
                    self.refreshView();
                } else {
                    self.$parent.flashSuccess = false;
                    self.$parent.flashError = translate_choice('addons.Meerkat::actions.approve_failed', data.approved.length) + data.errorMessage;
                }

                self.applyingBulkActions = false;
            }).catch(function (e) {
                var title = translate('addons.Meerkat::errors.comments_approve_plural');
                var genericError = translate('addons.Meerkat::errors.comments_approve_plural_desc');

                this.raiseError(title, genericError);
                self.applyingBulkActions = false;
            });
        },

        unApproveComment: function (item) {
            var self = this;
            var id = item.id;

            item.is_taking_action = true;
            item.is_unapproving = true;

            self.$http.post(self.ajax.unapprove, {ids: [id]}, function (data) {
                if (data.success) {
                    self.$parent.flashError = false;
                    self.$parent.flashSuccess = translate_choice('addons.Meerkat::actions.unapprove_success', data.unapproved.length);
                    _.each(data.unapproved, function (unApprovedId) {
                        var _tItem = _.findWhere(self.items, {id: unApprovedId});

                        self.changeItemToUnApproved(_tItem);
                        self.metrics.approved--;
                        self.metrics.pending++;
                        self.instanceChanges.commentUnApprovals.push(unApprovedId);
                    });


                    item.is_taking_action = false;
                    item.is_unapproving = false
                    self.refreshCounts();
                } else {
                    self.$parent.flashSuccess = false;
                    self.$parent.flashError = translate_choice('addons.Meerkat::actions.unapprove_failed', data.unapproved.length) + data.errorMessage;

                    item.is_taking_action = false;
                    item.is_unapproving = false;
                }
            }).catch(function (e) {
                var title = translate('addons.Meerkat::errors.comments_unapprove');
                var genericError = translate('addons.Meerkat::errors.comments_unapprove_desc');


                item.is_taking_action = false;
                item.is_unapproving = false;

                this.raiseError(title, genericError);
            });
        },

        unApproveMultiple: function () {
            var self = this;
            self.applyingBulkActions = true;
            self.$http.post(self.ajax.unapprove, {ids: self.checkedItems}, function (data) {
                if (data.success) {
                    self.$parent.flashError = false;
                    self.$parent.flashSuccess = translate_choice('addons.Meerkat::actions.unapprove_success', data.unapproved.length);
                    _.each(data.unapproved, function (unApprovedId) {
                        var _tItem = _.findWhere(self.items, {id: unApprovedId});

                        self.changeItemToUnApproved(_tItem);
                        self.metrics.approved--;
                        self.metrics.pending++;
                        self.instanceChanges.commentUnApprovals.push(unApprovedId);
                    });
                    self.refreshCounts();
                    self.refreshView();
                } else {
                    self.$parent.flashSuccess = false;
                    self.$parent.flashError = translate_choice('addons.Meerkat::actions.unapprove_failed', data.unapproved.length) + data.errorMessage;
                }

                self.applyingBulkActions = false;
            }).catch(function (e) {
                var title = translate('addons.Meerkat::errors.comments_unapprove_plural');
                var genericError = translate('addons.Meerkat::errors.comments_unapprove_desc_plural');
                self.applyBulkActions = false;

                this.raiseError(title, genericError);
            });
        },

        markItemAsSpam: function (item) {
            var self = this;
            var id = item.id;

            swal({
                type: 'warning',
                title: translate('addons.Meerkat::actions.prompt_confirm'),
                text: translate_choice('addons.Meerkat::actions.confirm_mark_as_spam', 1),
                confirmButtonText: translate('addons.Meerkat::actions.prompt_confirm_action'),
                cancelButtonText: translate('addons.Meerkat::actions.cancel'),
                showCancelButton: true
            }, function () {
                item.is_taking_action = true;
                item.is_markingspam = true;

                self.$http.post(self.ajax.spam, {ids: [id]}, function (data) {
                    if (data.comment_saved) {
                        self.$parent.flashError = false;
                        self.$parent.flashSuccess = translate_choice('addons.Meerkat::actions.spam_success', data.marked.length);
                        _.each(data.marked, function (markedAsSpam) {
                            var _tItem = _.findWhere(self.items, {id: markedAsSpam});

                            self.changeItemToIsSpam(_tItem);
                            self.metrics.spam++;
                            self.metrics.approved--;
                            self.instanceChanges.commentMarkedAsSpam.push(markedAsSpam);
                        });
                        self.refreshCounts();
                        item.is_taking_action = false;
                        item.is_markingspam = false;
                        item.spam = true;

                        if (data.specimen_sent == true && data.submit_success == false) {
                            var sampleErrors = {};
                            var title = translate('addons.Meerkat::errors.comments_mark_spam');
                            var errorMessages = data.errorMessage;
                            errorMessages.unshift(translate('addons.Meerkat::errors.guard_comment_saved_error'));
                            sampleErrors['spam'] = errorMessages;
                            self.raiseError(title, translate('addons.Meerkat::errors.guard_comment_saved_error'), sampleErrors);
                        }
                    } else {

                        item.is_taking_action = false;
                        item.is_markingspam = false;
                        self.$parent.flashSuccess = false;
                        self.$parent.flashError = translate_choice('addons.Meerkat::actions.spam_failed', data.marked.length);

                        if (data.specimen_sent && data.submit_success == false) {
                            var multipleErrors = translate('addons.Meerkat::errors.guard_multiple_spam_submit_errors');
                            var sampleErrors = {};
                            var title = translate('addons.Meerkat::errors.comments_mark_spam');
                            var errorMessages = data.errorMessage;
                            errorMessages.unshift(multipleErrors);
                            sampleErrors['spam'] = errorMessages;

                            self.raiseError(title, multipleErrors, sampleErrors);
                        } else {
                            item.is_taking_action = false;
                            item.is_markingspam = false;

                            var title = translate('addons.Meerkat::errors.comments_mark_spam');
                            var genericError = translate('addons.Meerkat::errors.comments_mark_spam_desc');
                            this.raiseError(title, genericError);
                        }
                    }
                }).catch(function (e) {
                    item.is_taking_action = false;
                    item.is_markingspam = false;

                    var title = translate('addons.Meerkat::errors.comments_mark_spam');
                    var genericError = translate('addons.Meerkat::errors.comments_mark_spam_desc');
                    this.raiseError(title, genericError);
                });
            });
        },

        markMultipleAsSpam: function () {
            var self = this;

            swal({
                type: 'warning',
                title: translate('addons.Meerkat::actions.prompt_confirm'),
                text: translate_choice('addons.Meerkat::actions.confirm_mark_as_spam', 1),
                confirmButtonText: translate('addons.Meerkat::actions.prompt_confirm_action'),
                cancelButtonText: translate('addons.Meerkat::actions.cancel'),
                showCancelButton: true
            }, function () {
                self.applyingBulkActions = true;
                self.$http.post(self.ajax.spam, {ids: self.checkedItems}, function (data) {
                    if (data.success) {
                        self.$parent.flashError = false;
                        self.$parent.flashSuccess = translate_choice('addons.Meerkat::actions.spam_success', data.marked.length);
                        _.each(data.marked, function (markedAsSpam) {
                            self.changeItemToIsSpam(markedAsSpam);
                            self.metrics.spam++;
                            self.metrics.approved--;
                            self.instanceChanges.commentMarkedAsSpam.push(markedAsSpam);
                        });
                        self.refreshCounts();
                        self.refreshView();
                    } else {
                        self.$parent.flashSuccess = false;
                        self.$parent.flashError = translate_choice('addons.Meerkat::actions.spam_failed', data.marked.length);
                    }

                    self.applyingBulkActions = false;
                }).catch(function (e) {
                    var title = translate('addons.Meerkat::errors.comments_mark_spam_plural');
                    var genericError = translate('addons.Meerkat::errors.comments_mark_spam_desc_plural');
                    self.applyingBulkActions = false;

                    this.raiseError(title, genericError);
                });
            });
        },

        markItemAsNotSpam: function (item) {
            var self = this;
            var id = item.id;

            swal({
                type: 'warning',
                title: translate('addons.Meerkat::actions.prompt_confirm'),
                text: translate_choice('addons.Meerkat::actions.confirm_mark_as_not_spam', 1),
                confirmButtonText: translate('addons.Meerkat::actions.prompt_confirm_action'),
                cancelButtonText: translate('addons.Meerkat::actions.cancel'),
                showCancelButton: true
            }, function () {
                item.is_taking_action = true;
                item.is_markingnotspam = true;

                self.$http.post(self.ajax.notspam, {ids: [id]}, function (data) {
                    if (data.comment_saved) {
                        self.$parent.flashError = false;
                        self.$parent.flashSuccess = translate_choice('addons.Meerkat::actions.not_spam_success', data.marked.length);
                        _.each(data.marked, function (markedAsNotSpam) {
                            self.changeItemToNotSpam(markedAsNotSpam);
                            self.metrics.spam--;
                            self.metrics.approved++;
                            self.instanceChanges.commentMarkedAsNotSpam.push(markedAsNotSpam);
                        });
                        item.is_taking_action = false;
                        item.is_markingnotspam = false;
                        item.spam = false;
                        self.refreshCounts();


                        if (data.specimen_sent == true && data.submit_success == false) {
                            var sampleErrors = {};
                            var title = translate('addons.Meerkat::errors.comments_mark_not_spam');
                            var errorMessages = data.errorMessage;
                            errorMessages.unshift(translate('addons.Meerkat::errors.guard_comment_ham_saved_error'));
                            sampleErrors['spam'] = errorMessages;
                            self.raiseError(title, translate('addons.Meerkat::errors.guard_comment_ham_saved_error'), sampleErrors);
                        }
                    } else {
                        self.$parent.flashSuccess = false;
                        item.is_taking_action = false;
                        item.is_markingnotspam = false;
                        self.$parent.flashError = translate_choice('addons.Meerkat::actions.not_spam_failed', data.marked.length);

                        if (data.specimen_sent && data.submit_success == false) {
                            var multipleErrors = translate('addons.Meerkat::errors.guard_multiple_ham_submit_errors');
                            var sampleErrors = {};
                            var title = translate('addons.Meerkat::errors.comments_mark_not_spam');
                            var errorMessages = data.errorMessage;
                            errorMessages.unshift(multipleErrors);
                            sampleErrors['spam'] = errorMessages;

                            self.raiseError(title, multipleErrors, sampleErrors);
                        } else {
                            item.is_taking_action = false;
                            item.is_markingspam = false;

                            var title = translate('addons.Meerkat::errors.comments_mark_not_spam');
                            var genericError = translate('addons.Meerkat::errors.comments_mark_not_spam_desc');
                            this.raiseError(title, genericError);
                        }
                    }
                }).catch(function (e) {
                    var title = translate('addons.Meerkat::errors.comments_mark_not_spam');
                    var genericError = translate('addons.Meerkat::errors.comments_mark_not_spam_desc');

                    item.is_taking_action = false;
                    item.is_markingnotspam = false;

                    this.raiseError(title, genericError);
                });
            });
        },

        markMultipleAsNotSpam: function () {
            var self = this;

            swal({
                type: 'warning',
                title: translate('addons.Meerkat::actions.prompt_confirm'),
                text: translate_choice('addons.Meerkat::actions.confirm_mark_as_not_spam', 1),
                confirmButtonText: translate('addons.Meerkat::actions.prompt_confirm_action'),
                cancelButtonText: translate('addons.Meerkat::actions.cancel'),
                showCancelButton: true
            }, function () {
                self.applyingBulkActions = true;
                self.$http.post(self.ajax.notspam, {ids: self.checkedItems}, function (data) {
                    if (data.success) {
                        self.$parent.flashError = false;
                        self.$parent.flashSuccess = translate_choice('addons.Meerkat::actions.not_spam_success', data.marked.length);
                        _.each(data.marked, function (markedAsNotSpam) {
                            self.changeItemToNotSpam(markedAsNotSpam);
                            self.metrics.approved++;
                            self.metrics.spam--;
                            self.instanceChanges.commentMarkedAsNotSpam.push(markedAsNotSpam);
                        });
                        self.refreshCounts();
                        self.refreshView();
                    } else {
                        self.$parent.flashSuccess = false;
                        self.$parent.flashError = translate_choice('addons.Meerkat::actions.not_spam_failed', data.marked.length);
                    }

                    self.applyingBulkActions = false;
                }).catch(function (e) {
                    var title = translate('addons.Meerkat::errors.comments_mark_not_spam_plural');
                    var genericError = translate('addons.Meerkat::errors.comments_mark_not_spam_desc_plural');
                    self.applyBulkActions = false;

                    this.raiseError(title, genericError);
                });
            });
        },

        applyBulkActions: function (action) {
            switch (action) {
                case 'delete':
                    this.deleteMultiple();
                    break;
                case 'approve':
                    this.approveMultiple();
                    break;
                case 'unapprove':
                    this.unApproveMultiple();
                    break;
                case 'spam':
                    this.markMultipleAsSpam();
                    break;
                case 'notspam':
                    this.markMultipleAsNotSpam();
                    break;
            }
        },

        enableReorder: function () {
            this.reordering = true;
            this.$broadcast('reordering.start');
        },

        cancelOrder: function () {
            this.reordering = false;
            this.$broadcast('reordering.stop');
        },

        saveOrder: function () {
            this.saving = true;
            var _vm = this;

            var order = _.map(this.items, function (item, i) {
                return item.id;
            });

            this.$http.post(this.ajax.reorder, {ids: order}, function () {
                _vm.saving = false;
                _vm.$broadcast('reordering.saved');
                _vm.loading = true;
                _vm.getItems();
                _vm.reordering = false;
            });
        },

        addActionPartial: function () {
            this.tableOptions.partials.actions = Meerkat.getMeerkatAddActionPartial();
            this.tableOptions.partials.bulkActions = Meerkat.getBulkActionsTempalte();
            this.tableOptions.partials.avatar = Meerkat.getAvatarTemplate();
        }
    }

});
