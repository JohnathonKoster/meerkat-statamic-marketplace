(function () {
    function protect() {
        if (!Meerkat.license.meerkat_license_public_domain) {
            return;
        }
        if (Meerkat.license.meerkat_license_valid && Meerkat.license.meerkat_license_on_correct_domain) {
            return;
        }
        if (Meerkat.license.meerkat_license_valid && !Meerkat.license.meerkat_license_on_correct_domain) {
           $('.flashdance').append($('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>' + translate('addons.Meerkat::errors.license_incorrect_domain') + '</div>'));
            return;
        }
        $('.flashdance').append($('<div class="alert alert-danger" role="alert">' + translate('addons.Meerkat::errors.license_no_license') + '</div>'));
    }
    $(document).ready(function () {
        protect();
    });
})();