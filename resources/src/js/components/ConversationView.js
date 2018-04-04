Vue.component('meerkat-conversation-view', {

    data: function () {
        return {
            isOpen: false,
            rootComment: null
        }
    },

    watch: {
        isOpen: function (value) {
            if (value) {
                $('body').addClass('meerkat-overflow-hidden');
            } else {
                $('body').removeClass('meerkat-overflow-hidden');
            }
        }
    },

    methods: {
        open: function (commentId) {
            this.$children[0].streamFor(commentId);
            this.$children[0].setIntendedComment(commentId);
            
            var _vm = this;
            this.$children[0].refreshView(function (items) {
                var rootComment = items.filter(function (item) {
                    return item.id == commentId;
                });

                if (rootComment.length > 0) {
                    _vm.rootComment = rootComment[0];
                }
            });
            this.$children[0].setSortingOrder('datestamp', 'asc');
            this.$children[0].setIntendedComment(commentId);
            this.isOpen = true;
        },
        close: function () {
            this.isOpen = false;
            if (this.$children[0].parentStateNeedsRefresh()) {
                var streamListing = this.getMainStreamListing();
                if (typeof streamListing !== 'undefined' && streamListing !== null) {
                    streamListing.refreshView();
                }
            }
        },
        getMainStreamListing: function () {
            for (var i = 0; i < this.$parent.$children.length; i++) {
                var child = this.$parent.$children[i];
                if (child instanceof MeerkatStreamListing) {
                    return child;
                }
            }
        }
    },

    components: {
        'meerkat-stream-listing': MeerkatStreamListing
    },

    ready: function () {
        this.$parent.meerkat_ConversationView = this;
        window.cpt = this.$parent;
        this.$children[0].overrideMobile();
    }

});