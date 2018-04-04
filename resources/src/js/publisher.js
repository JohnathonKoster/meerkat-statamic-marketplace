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