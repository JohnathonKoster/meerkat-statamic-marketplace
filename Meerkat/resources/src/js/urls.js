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
