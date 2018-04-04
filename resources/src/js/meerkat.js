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
