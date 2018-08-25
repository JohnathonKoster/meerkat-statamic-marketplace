Meerkat = {

    /**
     * Sets the bulk actions template.
     *
     * @param value
     * @returns {Meerkat}
     */
    setBulkActionsTemplate: function (value) {
        this.bulk_actions_template = value;
        return this;
    },

    getConversationLabel: function (collection) {
        return '';
    },

    /**
     * Gets the bulk actions template.
     *
     * @returns {string}
     */
    getBulkActionsTempalte: function() {
        return this.bulk_actions_template;
    },

    setAvatarTemplate: function(value) {
        this.avatar_tempalte = value;
        return this;
    },

    getAvatarTemplate: function() {
        return this.avatar_tempalte;
    },

    /**
     * Sets the Dossier table template.
     *
     * @param value
     * @returns {Meerkat}
     */
    setDossierTemplate: function (value) {
        this.dossier_template = value;
        return this;
    },

    /**
     * Gets the Dossier table template.
     *
     * @returns {string}
     */
    getDossierTemplate: function () {
        return this.dossier_template ? this.dossier_template : '';
    },

    /**
     * Sets the Dossier cell template.
     *
     * @param value
     * @returns {Meerkat}
     */
    setDossierCellTemplate: function (value) {
        this.dossier_cell_template = value;
        return this;
    },

    /**
     * Gets the Dossier cell template.
     *
     * @returns {string}
     */
    getDossierCellTemplate: function () {
        return this.dossier_cell_template ? this.dossier_cell_template : '';
    },

    /**
     * Sets the Meerkat cell template.
     *
     * @param value
     * @returns {Meerkat}
     */
    setMeerkatCellTemplate: function(value) {
        this.meerkat_cell_template = value;
        return this;
    },

    /**
     *Gets the Meerkat cell template.
     *
     * @returns {string}
     */
    getMeerkatCellTemplate: function() {
        return this.meerkat_cell_template ? this.meerkat_cell_template : '';
    },

    /**
     * Sets the Meerkat add action partial template.
     *
     * @param value
     * @returns {Meerkat}
     */
    setMeerkatAddActionPartialTemplate: function(value) {
        this.meerkat_add_action_template = value;
        return this;
    },

    /**
     * Gets the Meerkat add action partial.
     *
     * @returns {string}
     */
    getMeerkatAddActionPartial: function() {
      return this.meerkat_add_action_template ? this.meerkat_add_action_template : '';
    },

    compareVersionString: function(v1, comparator, v2) {
        "use strict";
        var comparator = comparator == '=' ? '==' : comparator;
        if(['==','===','<','<=','>','>=','!=','!=='].indexOf(comparator) == -1) {
            throw new Error('Invalid comparator. ' + comparator);
        }
        var v1parts = v1.split('.'), v2parts = v2.split('.');
        var maxLen = Math.max(v1parts.length, v2parts.length);
        var part1, part2;
        var cmp = 0;
        for(var i = 0; i < maxLen && !cmp; i++) {
            part1 = parseInt(v1parts[i], 10) || 0;
            part2 = parseInt(v2parts[i], 10) || 0;
            if(part1 < part2)
                cmp = 1;
            if(part1 > part2)
                cmp = -1;
        }
        return eval('0' + comparator + cmp);
    },

    compareStatamicVersion: function(comparator, desiredVersion) {
        return Meerkat.compareVersionString(Statamic.version, comparator, desiredVersion);
    }

};

/**
 * Executes a given callback when the user visits a given CP URL.
 *
 * @param url
 * @param callback
 */
function forUrl(url, callback) {
    if (Statamic.urlPath == Statamic.cpRoot + "/" + url) {
        return callback();
    }

    return null;
}

function whenPublisher(callback) {
    if (typeof Statamic.Publish !== 'undefined' && location.pathname.toLowerCase().endsWith('create') == false && location.pathname.toLowerCase().endsWith('create/') == false) {
        return callback();
    }

    return null;
}

/**
 * Returns a URL relative to the origin.
 *
 * @param relative
 * @returns {string}
 */
function originUrl(relative) {
    return window.location.origin + "/" + relative;
}

Meerkat.API = {

    /**
     * The comment count API URL.
     */
    getCommentCount: originUrl('!/Meerkat/api-comment-count'),

    /**
     * The all comments API URL.
     */
    getAllComments: originUrl('!/Meerkat/api-comments'),

    /**
     * The Meerkat addon URL.
     */
    addonPath: originUrl(Statamic.cpRoot + '/addons/meerkat'),

    /**
     * The name of the Meerkat form.
     */
    formName: 'meerkat'

};
Meerkat.Publisher = {

    publisherStream: '',

    setupNavigation: function () {
          var publisherNavigation = $('<ul class="nav nav-tabs" data-meerkat-publisher="tab-navigation"><li role="presentation" class="active" data-meerkat-publisher="tab:content"><a href="#" data-meerkat-publisher="toggle:content">' 
            + translate('addons.Meerkat::comments.content') + '</a></li><li role="presentation" data-meerkat-publisher="tab:comments"><a href="#" data-meerkat-publisher="toggle:comments">'
            + translate('addons.Meerkat::comments.comments') + '</a></li></ul>');
          $('[data-meerkat-publisher="fields-main"]').prepend(publisherNavigation);

          $('[data-meerkat-publisher="toggle:content"]').on('click', function (e) {
              $('[data-meerkat-publisher="tab:comments"]').removeClass('active');
              $('[data-meerkat-publisher="tab:content"]').addClass('active');

              $('[data-meerkat-publisher="comments-main"]').hide();
              $('[data-meerkat-publisher="publish-fields"]').show();
          });

          $('[data-meerkat-publisher="toggle:comments"]').on('click', function (e) {
              $('[data-meerkat-publisher="tab:comments"]').addClass('active');
              $('[data-meerkat-publisher="tab:content"]').removeClass('active');

              $('[data-meerkat-publisher="publish-fields"]').hide();
              $('[data-meerkat-publisher="comments-main"]').show();
          });
    },

    setupSecondaryCard: function () {
        var secondCard = '<div class="card" data-meerkat-publisher="comments-main"></div>';
        $('[data-meerkat-publisher="fields-main"]').append(secondCard);

        $('[data-meerkat-publisher="comments-main"]').append($(Meerkat.Publisher.publisherStream));

        $('[data-meerkat-publisher="comments-main"]').hide();
    },

    setupInstance: function () {
        var vm = new Vue({
            'el': '#meerkat-publisher-stream'
        });
    },

    setup: function () {
        if ($('#publish-fields').length) {
            $('#publish-fields').attr('data-meerkat-publisher', 'fields-main');
            $('#publish-fields .card:first').attr('data-meerkat-publisher', 'publish-fields');
            Meerkat.Publisher.setupSecondaryCard();
            Meerkat.Publisher.setupNavigation();
            Meerkat.Publisher.setupInstance();
        }
    }

};
/**
 * This will override the response count for the Meerkat form.
 */
forUrl('forms', function () {
    var meerkatCardHref = window.location.href + "/" + Meerkat.API.formName;
    var meerkatCard = document.querySelectorAll("a[href='" + meerkatCardHref + "']");


    if (Meerkat.compareStatamicVersion('<', '2.7.0')) {        
        if (meerkatCard.length > 0) {
            meerkatCard = meerkatCard[0];
            meerkatCard.setAttribute("href", Meerkat.API.addonPath);
            var cardMajor = meerkatCard.getElementsByTagName("span")[0];
    
            fetch(Meerkat.API.getCommentCount, {
                method: 'GET'
            }).then(function (response) {
                return response.json();
            }).then(function (data) {
                cardMajor.innerText = data.count;
            }).catch(function (err) {
                cardMajor.innerText = 0;
            });
        }
    } else {
        if (meerkatCard.length > 0) {
            var cardInner = $(meerkatCard[0]).parent().children().first('div.stat')[0];
            meerkatCard[0].setAttribute('href', Meerkat.API.addonPath);

            fetch(Meerkat.API.getCommentCount, {
                metod: 'GET'
            }).then(function (response) {
                return response.json();
            }).then(function (data) {
                cardInner.innerHTML = '<span class="icon icon-documents"></span> ' + data.count;
            }).catch(function (err) {
                cardInner.innerHTML = '<span class="icon icon-documents"></span> 0';
            });
        }
    }
});

/**
 * Redirect the user to the Meerkat addon path.
 */
forUrl('forms/' + Meerkat.API.formName, function () {
    window.location = Meerkat.API.addonPath;
});

(function () {
    $(document).ready(function () {
        whenPublisher(function () {
            /** Setup the publisher experience. */
            Meerkat.Publisher.setup();
        });
    });
})();
(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
"use strict";

Meerkat.setDossierTemplate("\n<div class=\"meerkat-bulk-action-wrapper pull-left\" v-if=\"showBulkActions\">\n    <partial name=\"bulkActions\"></partial>\n</div>\n\n<table class=\"dossier meerkat-comments-table table-striped\" v-if=\"sizes.md || sizes.lg\">\n    <thead v-if=\"hasHeaders\">\n        <tr>\n            <th class=\"checkbox-col\" v-if=\"hasCheckboxes\">\n                <input type=\"checkbox\" id=\"checkbox-all\" :checked=\"allItemsChecked\" @click=\"checkAllItems\" />\n                <label for=\"checkbox-all\"></label>\n            </th>\n\n            <th v-for=\"column in columns\"\n                @click=\"sortBy(column)\"\n                class=\"column-sortable\"\n                :class=\"['column-' + column.label, {'active': sortCol === column.field} ]\"\n            >\n                <template v-if=\"column.translation\">{{ column.translation }}</template>\n                <template v-else>{{ translate('cp.'+column.label) }}</template>\n                <i v-if=\"sortCol === column.field\"\n                    class=\"icon icon-chevron-{{ (sortOrders[column.field] > 0) ? 'up' : 'down' }}\"></i>\n            </th>\n        </tr>\n    </thead>\n    <tbody data-meerkat-type=\"comment\" v-el:tbody v-for=\"item in items | filterBy computedSearch | caseInsensitiveOrderBy computedSortCol computedSortOrder\">\n        <tr\n            data-meerkat-type=\"comment\"\n            data-meerkat-comment-id=\"{{ item['id'] }}\" data-meerkat-comment-published=\"{{ item['published'].toString() }}\"\n            data-meerkat-comment-spam=\"{{ item['spam'].toString() }}\">\n        <td colspan=\"3\">\n        <div class=\"comment-header-options\" v-if=\"item['published']\"><a href=\"{{ item['in_response_to_url'] }}#comment-{{ item['id'] }}\" target=\"_blank\" title=\"{{ translate('addons.Meerkat::actions.view_post_desc') }}\">{{ translate('addons.Meerkat::actions.view_post') }}</a></div>\n        <div class=\"float-left\"><a name=\"meerkat-comment-{{ item['id'] }}\"></a><span class=\"icon icon-flag\" v-if=\"item['published'] === false\"></span> {{{ item['in_response_string'] }}}</div>\n        </td>\n        </tr>\n        <tr data-meerkat-type=\"comment\"\n            data-meerkat-comment-id=\"{{ item['id'] }}\" data-meerkat-comment-published=\"{{ item['published'].toString() }}\"\n            data-meerkat-comment-spam=\"{{ item['spam'].toString() }}\">\n\n            <td class=\"checkbox-col\" v-if=\"hasCheckboxes && !reordering\">\n                <input type=\"checkbox\" :id=\"'checkbox-' + $index\" :checked=\"item.checked\" @change=\"toggle(item)\" />\n                <label :for=\"'checkbox-' + $index\"></label>\n            </td>\n\n            <td class=\"checkbox-col\" v-if=\"reordering\">\n                <div class=\"drag-handle\">\n                    <i class=\"icon icon-menu\"></i>\n                </div>\n            </td>\n\n            <td v-for=\"column in columns\" class=\"cell-{{ column.field }}\">\n                <partial name=\"cell\"></partial>\n            </td>\n        </tr>\n    </tbody>\n</table>\n<div v-if=\"sizes.sm || sizes.xs\" class=\"meerkat-mobile-table\">\n    <div v-for=\"item in items | filterBy computedSearch | caseInsensitiveOrderBy computedSortCol computedSortOrder\">\n        <div data-meerkat-mobile=\"wrap\" data-meerkat-type=\"comment\"\n            data-meerkat-comment-id=\"{{ item['id'] }}\" data-meerkat-comment-published=\"{{ item['published'].toString() }}\"\n            data-meerkat-comment-spam=\"{{ item['spam'].toString() }}\">\n            <partial name=\"cell\"></partial>                        \n        </div>\n    </div>\n</div>\n<div class=\"meerkat-pagination-wrapper\">\n    <ul class=\"pagination meerkat-pagination\">\n        <li v-if=\"$parent.pagination.prevPage\">\n            <a href=\"\" @click.prevent=\"call('previousPage')\" aria-label=\"{{ translate('addons.Meerkat::pagination.previous') }}\"><span>&laquo;</span></a>\n        </li>\n        <li v-for=\"page in $parent.pages\">\n            <a href=\"\" @click.prevent=\"call('goToPage', page.page)\" v-bind:class=\"{ 'active': page.active }\" :disabled=\"page.page === null\">{{ page.name }}</a>\n        </li>\n        <li v-if=\"$parent.pagination.nextPage\">\n            <a href=\"\" @click.prevent=\"call('nextPage')\" aria-label=\"{{ translate('addons.Meerkat::pagination.next') }}\"><span>&raquo;</span></a>\n        </li>\n    </ul>\n</div>\n");

},{}]},{},[1]);

//# sourceMappingURL=dossier.js.map

(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
"use strict";

Meerkat.setDossierCellTemplate("\n    <a v-if=\"$index === 0\" :href=\"item.edit_url\">\n        <span class=\"status status-{{ (item.published) ? 'live' : 'hidden' }}\"\n              :title=\"(item.published) ? translate('cp.published') : translate('cp.draft')\"\n        ></span>\n        {{ item[column.label] }}\n    </a>\n    <template v-else>\n        {{ item[column.label] }}\n    </template>\n");

},{}]},{},[1]);

//# sourceMappingURL=dossier_cell.js.map

(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
"use strict";

Meerkat.setMeerkatAddActionPartialTemplate("\n    <li v-if=\"item['published'] === false\"><a href=\"#\" @click.prevent=\"call('approveComment', item.id)\" title=\"{{ translate('addons.Meerkat::actions.approve_desc') }}\"><span class=\"icon icon-check\"></span> {{ translate('addons.Meerkat::actions.approve') }}</a></li>\n    <li v-if=\"item['published'] === true\"><a href=\"#\" @click.prevent=\"call('unApproveComment', item.id)\" title=\"{{ translate('addons.Meerkat::actions.unapprove_desc') }}\">{{ translate('addons.Meerkat::actions.unapprove') }}</a></li>\n    <li><a href=\"#\" @click.prevent=\"call('replyToComment', item.id)\" title=\"{{ translate('addons.Meerkat::actions.reply_desc') }}\"><span class=\"icon icon-reply\"></span> {{ translate('addons.Meerkat::actions.reply') }}</a></li>\n    <li><a href=\"#\" @click.prevent=\"call('editComment', item.id)\" title=\"{{ translate('addons.Meerkat::actions.edit_desc') }}\"><span class=\"icon icon-edit\"></span> {{ translate('addons.Meerkat::actions.edit') }}</a></li>\n    <li v-if=\"item['spam'] === false\"><a href=\"#\" @click.prevent=\"call('markItemAsSpam', item.id)\" title=\"{{ translate('addons.Meerkat::actions.spam_desc') }}\"><span class=\"icon icon-shield\"></span> {{ translate('addons.Meerkat::actions.spam') }}</a></li>\n    <li v-if=\"item['spam'] === true\"><a href=\"#\" @click.prevent=\"call('markItemAsNotSpam', item.id)\" title=\"{{ translate('addons.Meerkat::actions.not_spam_desc') }}\"><span class=\"icon icon-shield\"></span> {{ translate('addons.Meerkat::actions.not_spam') }}</a></li>\n    <li><a href=\"#\" @click.prevent=\"call('deleteItem', item.id)\" title=\"{{ translate('addons.Meerkat::actions.delete_desc') }}\"><span class=\"icon icon-trash\"></span> {{ translate('addons.Meerkat::actions.delete') }}</a></li>\n");

},{}]},{},[1]);

//# sourceMappingURL=add_action.js.map

(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
"use strict";

Meerkat.setMeerkatCellTemplate("\n<div v-if=\"sizes.md || sizes.lg\">\n<div class=\"media\" v-if=\"$index === 0\">\n    <partial name=\"avatar\"></partial>\n    <div class=\"media-body\"> <h4 class=\"media-heading\">{{ item['name'] }}</h4>\n        <span class=\"icon icon-mail\"></span> <a href=\"mailto:{{ item['email'] }}\">{{ item['email'] }}</a><br />\n        \n        <div v-if=\"item['url']\"><a href=\"{{ item['url'] }}\" target=\"_blank\"><span class=\"icon icon-globe\"></span> {{ item['url'] }}</a></div>\n    </div>\n</div>\n<div v-if=\"$index === 1\">\n    <div data-meerkat-comment=\"response\" v-if=\"item['is_reply']\">\n    {{ translate('addons.Meerkat::comments.in_reply_to_simple') }} <a @click.prevent=\"call('openConversation', item['parent_comment_id'])\">{{ item['parent_comment_name'] }}</a>\n    </div>\n\n    <div data-meerkat-comment=\"content\" v-if=\"item['editing'] === false\">\n    {{{ item['comment'] }}}\n    </div>\n    <div v-if=\"item['editing'] === true\">\n        <div class=\"markdown-fieldtype\">\n            <markdown-fieldtype :data.sync=\"item['original_markdown']\"></markdown-fieldtype>\n        </div>\n        <ul class=\"list-inline\" data-meerkat-has=\"actions\">\n        <li v-if=\"item['saving'] === false\"><a href=\"#\" @click.prevent=\"call('saveItemEdits', item.id)\"><span class=\"icon icon-check\"></span> {{ translate('addons.Meerkat::actions.save') }}</a></li>\n        <li v-if=\"item['saving'] === false\"><a href=\"#\" @click.prevent=\"call('cancelItemEdit', item.id)\">{{ translate('addons.Meerkat::actions.cancel') }}</a></li>\n        <li v-if=\"item['saving'] === true\"><span class=\"icon icon-circular-graph animation-spin\"></span> {{ translate('addons.Meerkat::actions.saving') }}</li>\n        </ul>\n    </div>\n    <div v-if=\"item['writing_reply'] === true\">\n        <div class=\"markdown-fieldtype\">\n            <markdown-fieldtype :data.sync=\"item['new_reply']\"></markdown-fieldtype>\n        </div>\n        <ul class=\"list-inline\" data-meerkat-has=\"actions\">\n        <li v-if=\"item['saving'] === false\"><a href=\"#\" @click.prevent=\"call('createNewReply', item.id)\"><span class=\"icon icon-reply\"></span> {{ translate('addons.Meerkat::actions.reply') }}</a></li>\n        <li v-if=\"item['saving'] === false\"><a href=\"#\" @click.prevent=\"call('cancelPostReply', item.id)\">{{ translate('addons.Meerkat::actions.cancel') }}</a></li>\n        <li v-if=\"item['saving'] === true\"><span class=\"icon icon-circular-graph animation-spin\"></span> {{ translate('addons.Meerkat::actions.replying') }}</li>\n        </ul>\n    </div>\n    <div data-meerkat-type=\"comment\" data-meerkat-has=\"actions\"\n         v-if=\"hasActions\">\n    <ul class=\"list-inline\" v-if=\"item['editing'] === false && item['writing_reply'] === false\">\n    <partial name=\"actions\"></partial>\n    </ul>\n    </div>\n</div>\n<div v-if=\"$index === 3\">\n    <a href=\"/cp/collections/entries{{ item['in_response_to_edit_url'] }}\">{{ item['in_response_to'] }}</a><br />\n    <div v-if=\"item['published']\">\n        <small><a href=\"{{ item['in_response_to_url'] }}#comment-{{ item['id'] }}\" target=\"_blank\" title=\"{{ translate('addons.Meerkat::actions.view_post_desc') }}\">{{ translate('addons.Meerkat::actions.view_post') }}</a></small><br />\n    </div>\n    <span class=\"label label-default\"><span class=\"icon icon-chat\"></span> {{ item['comment_count'] }}</span>\n</div>\n</div>\n<div v-if=\"sizes.sm || sizes.xs\">\n    <div class=\"meerkat-mobile-card\" id=\"meerkat-mobile-comment-card-{{ item['id'] }}\" v-bind:class=\"{ 'meerkat-conversation-intended-comment': ($parent.$parent.intendedComment != null && $parent.$parent.intendedComment == item['id']) }\">\n        <div class=\"meerkat-mobile-card-header\" v-bind:class=\"{ 'is-selected': item.checked }\">\n            <div class=\"meerkat-mobile-card-avatar\">\n                <partial name=\"avatar\"></partial>\n            </div>\n            <a class=\"meerkat-mobile-card-indicator\" v-bind:class=\"{ 'checked': item.checked }\" @click.prevent=\"$parent.toggle(item)\"></a>\n            <h5>\n                {{ item['name'] }}\n                <span v-if=\"item['is_reply']\">\n                    <span v-if=\"$parent.$parent.loadStreamFor !== null\">\n                        - {{ translate('addons.Meerkat::comments.in_reply_to_simple') }} {{ item['parent_comment_name'] }}\n                    </span>\n                    <span v-else>\n                        - {{ translate('addons.Meerkat::comments.in_reply_to_simple') }} <a @click.prevent=\"call('openConversation', item['parent_comment_id'])\">{{ item['parent_comment_name'] }}\n                        <span v-if=\"item['conversation_participants'].length - 2 > 0\">( +{{ item['conversation_participants'].length - 2 }} {{ Meerkat.getConversationLabel([]) }}\n                            <span v-if=\"item['conversation_participants'].length - 2 == 1\">{{ translate('addons.Meerkat::comments.conversation_other_singular') }}</span>\n                            <span v-if=\"item['conversation_participants'].length - 2 > 1\">{{ translate('addons.Meerkat::comments.conversation_other_plural') }}</span>\n                        )</span></a>\n                    </span>\n                </span>\n            </h5>\n            <h6><a href=\"mailto:{{ item['email'] }}\">{{ item['email'] }}</a></h6>\n        </div>\n        <div class=\"meerkat-mobile-card-content\">\n            <div class=\"comment-header-options meerkat-mobile-card-reply-to\">\n                {{{ item['in_response_string'] }}}\n            </div>\n\n            <div data-meerkat-comment=\"content\" v-if=\"item['editing'] === false\">\n            {{{ item['comment'] }}}\n            </div>\n            <div v-if=\"item['editing'] === true\">\n                <div class=\"markdown-fieldtype\">\n                    <markdown-fieldtype :data.sync=\"item['original_markdown']\"></markdown-fieldtype>\n                </div>\n                <ul class=\"list-inline meerkat-mobile-edit-actions\" data-meerkat-has=\"actions\">\n                <li v-if=\"item['saving'] === false\"><a href=\"#\" @click.prevent=\"call('saveItemEdits', item.id)\"><span class=\"icon icon-check\"></span> {{ translate('addons.Meerkat::actions.save') }}</a></li>\n                <li v-if=\"item['saving'] === false\"><a href=\"#\" @click.prevent=\"call('cancelItemEdit', item.id)\">{{ translate('addons.Meerkat::actions.cancel') }}</a></li>\n                <li v-if=\"item['saving'] === true\"><span class=\"icon icon-circular-graph animation-spin\"></span> {{ translate('addons.Meerkat::actions.saving') }}</li>\n                </ul>\n            </div>\n            <div v-if=\"item['writing_reply'] === true\">\n                <div class=\"markdown-fieldtype\">\n                    <markdown-fieldtype :data.sync=\"item['new_reply']\"></markdown-fieldtype>\n                </div>\n                <ul class=\"list-inline meerkat-mobile-edit-actions\" data-meerkat-has=\"actions\">\n                <li v-if=\"item['saving'] === false\"><a href=\"#\" @click.prevent=\"call('createNewReply', item.id)\"><span class=\"icon icon-reply\"></span> {{ translate('addons.Meerkat::actions.reply') }}</a></li>\n                <li v-if=\"item['saving'] === false\"><a href=\"#\" @click.prevent=\"call('cancelPostReply', item.id)\">{{ translate('addons.Meerkat::actions.cancel') }}</a></li>\n                <li v-if=\"item['saving'] === true\"><span class=\"icon icon-circular-graph animation-spin\"></span> {{ translate('addons.Meerkat::actions.replying') }}</li>\n                </ul>\n            </div>\n        </div>\n        <div class=\"meerkat-mobile-card-footer\" v-if=\"item['editing'] === false && item['writing_reply'] === false\">\n            <div data-meerkat-type=\"comment\" data-meerkat-has=\"actions\"\n                    v-if=\"hasActions\">\n                <ul class=\"list-inline meerkat-mobile-card-actions\" v-if=\"item['editing'] === false && item['writing_reply'] === false\">\n                <partial name=\"actions\"></partial>\n                </ul>\n            </div>\n        </div>\n    </div>\n</div>\n");

},{}]},{},[1]);

//# sourceMappingURL=stream_cell.js.map

(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
"use strict";

Meerkat.setBulkActionsTemplate("\n<select class=\"meerkat-bulk-actions form-control\" v-model=\"bulkAction\">\n    <option value=\"delete\" selected>{{ translate_choice('addons.Meerkat::actions.bulk_delete', checkedItems.length) }} ({{ checkedItems.length }} {{ translate_choice('addons.Meerkat::comments.comments_c', checkedItems.length) }})</option>\n    <option value=\"approve\">{{ translate_choice('addons.Meerkat::actions.bulk_approve', checkedItems.length) }} ({{ checkedItems.length }} {{ translate_choice('addons.Meerkat::comments.comments_c', checkedItems.length) }})</option>\n    <option value=\"unapprove\">{{ translate_choice('addons.Meerkat::actions.bulk_unapprove', checkedItems.length) }} ({{ checkedItems.length }} {{ translate_choice('addons.Meerkat::comments.comments_c', checkedItems.length) }})</option>\n    <option value=\"spam\">{{ translate_choice('addons.Meerkat::actions.bulk_mark_spam', checkedItems.length) }} ({{ checkedItems.length }} {{ translate_choice('addons.Meerkat::comments.comments_c', checkedItems.length) }})</option>\n    <option value=\"notspam\">{{ translate_choice('addons.Meerkat::actions.bulk_mark_not_spam', checkedItems.length) }} ({{ checkedItems.length }} {{ translate_choice('addons.Meerkat::comments.comments_c', checkedItems.length) }})</option>\n</select>\n<a href=\"#\" @click.prevent=\"call('applyBulkActions', bulkAction)\" class=\"btn btn-default\" v-if=\"$parent.applyingBulkActions == false\"><span class=\"icon icon-tools\"></span> {{ translate('addons.Meerkat::actions.apply') }}</a>\n<span v-if=\"$parent.applyingBulkActions == true\"><span class=\"icon icon-circular-graph animation-spin\"></span> {{ translate('addons.Meerkat::actions.applying') }}</span>\n");

},{}]},{},[1]);

//# sourceMappingURL=bulkactions.js.map

Vue.filter('strlimit', function(value, length) {
    if (value.toString().length <= length) {
        return value;
    }

    return value.toString().substring(0, length) + '...';
});
window.DossierTable = DossierTable = Vue.component('DossierTable', {

    template: Meerkat.getDossierTemplate(),

    props: ['options', 'keyword'],

    data: function () {
        return {
            items: [],
            columns: [],
            sortCol: this.options.sort || null,
            sortOrder: this.options.sortOrder || 'asc',
            sortOrders: {},
            reordering: false,
            sizes: {
                xs: false,
                sm: false,
                md: false,
                lg: false
            },
            shouldOverrideToMobile: false        }
    },

    partials: {
        // The default cell markup will be a link to the edit_url with a status symbol
        // if it's the first cell. Remaining cells just get the label.
        cell: Meerkat.getDossierCellTemplate()
    },

    computed: {

        hasCheckboxes: function () {
            if (this.options.checkboxes === false) {
                return false;
            }

            return true;
        },

        itemsAreChecked: function () {
            return this.checkedItems.length > 0;
        },

        hasSearch: function () {
            if (this.options.search === false) {
                return false;
            }

            return true;
        },

        hasHeaders: function () {
            if (this.options.headers === false) {
                return false;
            }

            return true;
        },

        hasActions: function () {
            return this.options.partials.actions !== undefined
                && this.options.partials.actions !== '';
        },

        showBulkActions: function () {
            return (this.hasItems && this.hasCheckboxes && this.itemsAreChecked && !this.reordering);
        },

        hasItems: function () {
            return this.$parent.hasItems;
        },

        reorderable: function () {
            return this.options.reorderable;
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

        computedSearch: function () {
            if (this.reordering) {
                return null;
            }

            return this.keyword;
        },

        computedSortCol: function () {
            if (this.reordering) {
                return false;
            }

            return this.sortCol;
        },

        computedSortOrder: function () {
            if (this.reordering) {
                return false;
            }

            return this.sortOrders[this.sortCol];
        }
    },

    beforeCompile: function () {
        var self = this;

        _.each(self.options.partials, function (str, name) {
            self.$options.partials[name] = str;
        });
    },

    ready: function () {
        this.items = this.$parent.items;
        this.columns = this.$parent.columns;

        this.setColumns();
        this.setSortOrders();

        this.sortCol = this.options.sort || this.columns[0].field;

        if (typeof this.$parent !== 'undefined' && this.$parent !== null) {
            if (typeof this.$parent.passMobileOverride !== 'undefined') {
                if (this.$parent.passMobileOverride) {
                    this.shouldOverrideToMobile = true;
                }
            }
        }

        window.addEventListener('resize', this.handleResize);
        this.handleResize();
    },

    beforeDestroy: function () {
        window.removeEventListener('resize', this.handleResize);
    },

    methods: {

        setIntended: function (intended) {
            this.intendedComment = intended;
        },

        handleResize: function () {

            if (this.shouldOverrideToMobile) {
                this.sizes.xs = true;
                this.sizes.sm = false;
                this.sizes.md = false;
                this.sizes.lg = false;
                return;
            }

            var width = window.innerWidth;

            if (width <= 767) {
                this.sizes.xs = true;
                this.sizes.sm = false;
                this.sizes.md = false;
                this.sizes.lg = false;
                return;
            }
            if (width >= 768 && width <= 991) {
                this.sizes.xs = false;
                this.sizes.sm = true;
                this.sizes.md = false;
                this.sizes.lg = false;
                return;
            }
            if (width >= 992 && width <= 1199) {
                this.sizes.xs = false;
                this.sizes.sm = false;
                this.sizes.md = true;
                this.sizes.lg = false;
                return;
            }
            if (width >= 1200) {
                this.sizes.xs = false;
                this.sizes.sm = false;
                this.sizes.md = false;
                this.sizes.lg = true;
                return;
            }
        },
        
        registerPartials: function () {
            var self = this;

            _.each(self.options.partials, function (str, name) {
                Vue.partial(name, str);
            });
        },

        setColumns: function () {
            var columns = [];
            _.each(this.columns, function (column) {
                if (typeof column === 'object') {
                    columns.push({label: column.label, field: column.field, translation: column.translation});
                } else {
                    columns.push({label: column, field: column});
                }
            });
            this.columns = columns;
        },

        setSortOrders: function () {
            var sortOrders = {};
            _.each(this.columns, function (col) {
                sortOrders[col.field] = 1;
            });

            // Apply the initial sort order
            sortOrders[this.sortCol] = (this.sortOrder === 'asc') ? 1 : -1;

            this.sortOrders = sortOrders;
        },

        sortBy: function (col) {
            if (this.sortCol === col.field) {
                this.sortOrders[col.field] = this.sortOrders[col.field] * -1;
            }

            this.sortCol = col.field;
        },

        checkAllItems: function () {
            var status = !this.allItemsChecked;

            _.each(this.items, function (item) {
                item.checked = status;
            });
        },

        toggle: function (item) {
            item.checked = !item.checked;
        },

        enableReorder: function () {
            var self = this;

            self.reordering = true;

            $(this.$els.tbody).sortable({
                axis: 'y',
                revert: 175,
                placeholder: 'placeholder',
                handle: '.drag-handle',
                forcePlaceholderSize: true,

                start: function (e, ui) {
                    ui.item.data('start', ui.item.index())
                },

                update: function (e, ui) {
                    var start = ui.item.data('start'),
                        end = ui.item.index();

                    self.items.splice(end, 0, self.items.splice(start, 1)[0]);
                }

            });
        },

        disableReorder: function () {
            this.reordering = false;
            $(this.$els.tbody).sortable('destroy');
        },

        saveOrder: function () {
            this.$parent.saveOrder();
        },

        /**
         * Dynamically call a method on the parent component
         *
         * Eg. `call('foo', 'bar', 'baz')` would be the equivalent
         * of doing `this.$parent.foo('bar', 'baz')`
         */
        call: function (method) {
            var args = Array.prototype.slice.call(arguments, 1);
            this.$parent[method].apply(this, args);
        }
    },

    events: {
        'reordering.start': function () {
            this.enableReorder();
        },
        'reordering.saved': function () {
            this.reordering = false;
        },
        'reordering.stop': function () {
            this.disableReorder();
        }
    }
});
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
        if (this.can('super')) {
            this.addActionPartial();
        }

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

                if (_vm.tableOptions.paginate == true) {
                    _vm.items = data.items.data;
                    _vm.pagination.from = data.items.from;
                    _vm.pagination.to = data.items.to;
                    _vm.pagination.prevPage = data.items.prev_page_url;
                    _vm.pagination.nextPage = data.items.next_page_url;
                    _vm.pagination.lastPage = data.items.last_page;
                    _vm.pagination.page = _vm.tableOptions.currentPage;
                } else {
                    _vm.items = data.items;
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

        editComment: function (id) {
            var item = _.findWhere(this.items, {id: id});
            item.editing = true;
        },

        replyToComment: function (id) {
            var item = _.findWhere(this.items, {id: id});
            item.writing_reply = true;
        },
        raiseError: function (messageTitle, fallbackMessage, data) {
            if (typeof data !== 'undefined' && data != null && data.length > 0) {
                var dataMessage = '<ul>';

                var _errorMesssages = data.length,
                    _i = 0;
                
                for (_i; _i < _errorMesssages; _i++) {
                    dataMessage += '<li>' + data[_i] + '</li>';
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
        createNewReply: function (id) {
            var self = this;
            var item = _.findWhere(this.items, {id: id});

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
                    self.$parent.flashSuccess = false;
                    self.$parent.flashError = translate('addons.Meerkat::actions.save_failure') + data.errorMessage;
                }

                // Indicate that we are no longer saving the comment, regardless of how it went.
                item.saving = false;
            }).catch(function (e) {
                var title = translate('addons.Meerkat::errors.comments_create_reply');
                var validationError = translate('addons.Meerkat::errors.comments_create_reply_validation');
                var genericError = translate('addons.Meerkat::errors.comments_create_reply_generic');

                if (typeof e.data !== 'undefined' && typeof e.data.errors !== undefined && e.data.errors.length > 0) {
                    this.raiseError(title, validationError, e.data.errors);
                } else {    
                    this.raiseError(title, genericError);
                }

                item.saving = false;
            });
        },

        cancelPostReply: function (id) {
            var item = _.findWhere(this.items, {id: id});
            item.writing_reply = false;
        },

        cancelItemEdit: function (id) {
            var item = _.findWhere(this.items, {id: id});
            item.editing = false;
        },

        saveItemEdits: function (id) {
            var self = this;
            var item = _.findWhere(this.items, {id: id});

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
                }

                // Indicate that we are no longer saving the comment, regardless of how it went.
                item.saving = false;
            }).catch(function (e) {
                var title = translate('addons.Meerkat::errors.comments_save');
                var validationError = translate('addons.Meerkat::errors.comments_save_validation');
                var genericError = translate('addons.Meerkat::errors.comments_save_generic');

                if (typeof e.data !== 'undefined' && typeof e.data.errors !== undefined && e.data.errors.length > 0) {
                    this.raiseError(title, validationError, e.data.errors);
                } else {    
                    this.raiseError(title, genericError);
                }

                item.saving = false;
            });
        },

        removeItemFromList: function (id) {
            var item = _.findWhere(this.items, {id: id});
            var index = _.indexOf(this.items, item);
            this.items.splice(index, 1);
        },

        changeItemToApprove: function (id) {
            var item = _.findWhere(this.items, {id: id});
            item.published = true;
        },

        changeItemToUnApproved: function (id) {
            var item = _.findWhere(this.items, {id: id});
            item.published = false;
        },

        changeItemToIsSpam: function (id) {
            var item = _.findWhere(this.items, {id: id});
            item.spam = true;
        },

        changeItemToNotSpam: function (id) {
            var item = _.findWhere(this.items, {id: id});
            item.spam = false;
        },

        deleteItem: function (id) {
            var self = this;

            swal({
                type: 'warning',
                title: translate('addons.Meerkat::actions.prompt_confirm'),
                text: translate_choice('addons.Meerkat::actions.confirm_delete_comment', 1),
                confirmButtonText: translate('addons.Meerkat::actions.prompt_confirm_action'),
                cancelButtonText: translate('addons.Meerkat::actions.cancel'),
                showCancelButton: true
            }, function () {
                self.$http.delete(self.ajax.delete, {ids: [id]}, function (data) {
                    _.each(data.removed, function (removedId) {
                        self.removeItemFromList(removedId);
                        self.metrics.all--;
                    });
                    // Just in case ;)
                    self.removeItemFromList(id);
                    self.instanceChanges.commentRemovals.push(id);
                    self.refreshCounts();
                }).catch(function (e) {
                    var title = translate('addons.Meerkat::errors.comments_remove');
                    var genericError = translate('addons.Meerkat::errors.comments_remove_desc');

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

                    this.raiseError(title, genericError);
                    self.applyBulkActions = false;
                });
            });
        },

        approveComment: function (id) {
            var self = this;
            self.applyingBulkActions = true;
            self.$http.post(self.ajax.approve, {ids: [id]}, function (data) {
                if (data.success) {
                    self.$parent.flashSuccess = translate_choice('addons.Meerkat::actions.approve_success', data.approved.length);
                    _.each(data.approved, function (approvedId) {
                        self.changeItemToApprove(approvedId);
                        self.metrics.approved++;
                        self.metrics.pending--;
                        self.instanceChanges.commentApprovals.push(approvedId);
                    });
                    self.refreshCounts();
                } else {
                    self.$parent.flashSuccess = false;
                    self.$parent.flashError = translate_choice('addons.Meerkat::actions.approve_failed', data.approved.length) + data.errorMessage;
                }
            }).catch(function (e) {
                var title = translate('addons.Meerkat::errors.comments_approve');
                var genericError = translate('addons.Meerkat::errors.comments_approve_desc');

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
                        self.changeItemToApprove(approvedId);
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

        unApproveComment: function (id) {
            var self = this;

            self.$http.post(self.ajax.unapprove, {ids: [id]}, function (data) {
                if (data.success) {
                    self.$parent.flashError = false;
                    self.$parent.flashSuccess = translate_choice('addons.Meerkat::actions.unapprove_success', data.unapproved.length);
                    _.each(data.unapproved, function (unApprovedId) {
                        self.changeItemToUnApproved(unApprovedId);
                        self.metrics.approved--;
                        self.metrics.pending++;
                        self.instanceChanges.commentUnApprovals.push(unApprovedId);
                    });
                    self.refreshCounts();
                } else {
                    self.$parent.flashSuccess = false;
                    self.$parent.flashError = translate_choice('addons.Meerkat::actions.unapprove_failed', data.unapproved.length) + data.errorMessage;
                }
            }).catch(function (e) {
                var title = translate('addons.Meerkat::errors.comments_unapprove');
                var genericError = translate('addons.Meerkat::errors.comments_unapprove_desc');

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
                        self.changeItemToUnApproved(unApprovedId);
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

                this.raiseError(title, genericError);
                self.applyBulkActions = false;
            });
        },

        markItemAsSpam: function (id) {
            var self = this;

            swal({
                type: 'warning',
                title: translate('addons.Meerkat::actions.prompt_confirm'),
                text: translate_choice('addons.Meerkat::actions.confirm_mark_as_spam', 1),
                confirmButtonText: translate('addons.Meerkat::actions.prompt_confirm_action'),
                cancelButtonText: translate('addons.Meerkat::actions.cancel'),
                showCancelButton: true
            }, function () {
                self.$http.post(self.ajax.spam, {ids: [id]}, function (data) {
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
                    } else {
                        self.$parent.flashSuccess = false;
                        self.$parent.flashError = translate_choice('addons.Meerkat::actions.spam_failed', data.marked.length);
                    }
                }).catch(function (e) {
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

                    this.raiseError(title, genericError);
                    self.applyingBulkActions = false;
                });
            });
        },

        markItemAsNotSpam: function (id) {
            var self = this;

            swal({
                type: 'warning',
                title: translate('addons.Meerkat::actions.prompt_confirm'),
                text: translate_choice('addons.Meerkat::actions.confirm_mark_as_not_spam', 1),
                confirmButtonText: translate('addons.Meerkat::actions.prompt_confirm_action'),
                cancelButtonText: translate('addons.Meerkat::actions.cancel'),
                showCancelButton: true
            }, function () {
                self.$http.post(self.ajax.notspam, {ids: [id]}, function (data) {
                    if (data.success) {
                        self.$parent.flashError = false;
                        self.$parent.flashSuccess = translate_choice('addons.Meerkat::actions.not_spam_success', data.marked.length);
                        _.each(data.marked, function (markedAsNotSpam) {
                            self.changeItemToNotSpam(markedAsNotSpam);
                            self.metrics.spam--;
                            self.metrics.approved++;
                            self.instanceChanges.commentMarkedAsNotSpam.push(markedAsNotSpam);
                        });
                        self.refreshCounts();
                    } else {
                        self.$parent.flashSuccess = false;
                        self.$parent.flashError = translate_choice('addons.Meerkat::actions.not_spam_failed', data.marked.length);
                    }
                }).catch(function (e) {
                    var title = translate('addons.Meerkat::errors.comments_mark_not_spam');
                    var genericError = translate('addons.Meerkat::errors.comments_mark_not_spam_desc');

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

                    this.raiseError(title, genericError);
                    self.applyBulkActions = false;
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
//# sourceMappingURL=meerkat.js.map
