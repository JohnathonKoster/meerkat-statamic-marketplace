Meerkat.Publisher = {

    publisherStream: '',
    contentWrap: null,

    setupNavigation: function () {
          var publisherNavigation = $('<ul class="nav nav-tabs" style="width:100%;" data-meerkat-publisher="tab-navigation"><li role="presentation" class="active" data-meerkat-publisher="tab:content"><a href="#" data-meerkat-publisher="toggle:content">'
            + translate('addons.Meerkat::comments.content') + '</a></li><li role="presentation" data-meerkat-publisher="tab:comments"><a href="#" data-meerkat-publisher="toggle:comments">'
            + translate('addons.Meerkat::comments.comments') + '</a></li></ul>');
          $('[data-meerkat-publisher="fields-main"]').parent().prepend(publisherNavigation);

          $('[data-meerkat-publisher="toggle:content"]').on('click', function (e) {
              $('[data-meerkat-publisher="tab:comments"]').removeClass('active');
              $('[data-meerkat-publisher="tab:content"]').addClass('active');

              $('[data-meerkat-publisher="comments-main"]').hide();
              $(window.Meerkat.Publisher.contentWrap).show();
          });

          $('[data-meerkat-publisher="toggle:comments"]').on('click', function (e) {
              $('[data-meerkat-publisher="tab:comments"]').addClass('active');
              $('[data-meerkat-publisher="tab:content"]').removeClass('active');

              $(window.Meerkat.Publisher.contentWrap).hide();
              $('[data-meerkat-publisher="comments-main"]').show();
          });
    },

    setupSecondaryCard: function () {
        var secondCard = '<div class="card" data-meerkat-publisher="comments-main"></div>';
        $('[data-meerkat-publisher="fields-main"]').parent().append(secondCard);

        $('[data-meerkat-publisher="comments-main"]').append($(Meerkat.Publisher.publisherStream));

        $('[data-meerkat-publisher="comments-main"]').hide();
    },

    setupInstance: function () {
        var vm = new Vue({
            'el': '#meerkat-publisher-stream'
        });
    },

    setup: function () {
        console.log('inside publish setup');
        var publishWrap = null;

        if ($('#publish-fields').length) {
            publishWrap = '#publish-fields';
        } else if ($('.publish-fields')) {
            publishWrap = '.publish-fields:first';
        }

        if (publishWrap != null) {
            window.Meerkat.Publisher.contentWrap = $(publishWrap);
            $(publishWrap).attr('data-meerkat-publisher', 'fields-main');
            $(publishWrap + ' .card:first').attr('data-meerkat-publisher', 'publish-fields');
            Meerkat.Publisher.setupSecondaryCard();
            Meerkat.Publisher.setupNavigation();
            Meerkat.Publisher.setupInstance();
        }
    }

};