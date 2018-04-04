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