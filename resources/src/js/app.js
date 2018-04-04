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