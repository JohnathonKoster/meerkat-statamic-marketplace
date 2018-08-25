(function () {

    $(document).ready(function () {
        var cpNav = $('a[href$="meerkat?source=cp-nav"]');
        window.meerkatAutoUpdateInterval = window.setInterval(function () {
            $.get(window.Meerkat.countsUrl, function (data) {
               if (typeof data !== 'object') {
                   return;
               }

               if (data.counts.pending > 0) {
                   if (cpNav.find('.badge').length > 0) {
                       cpNav.find('.badge').text(data.counts.pending);
                   } else {
                       var badge = $('<span class="badge bg-red">' + data.counts.pending + '</span>');
                       cpNav.append(badge);
                   }
               }

                $('[data-meerkat-stats="all"]').text(data.counts.all);
                $('[data-meerkat-stats="pending"]').text(data.counts.pending);
                $('[data-meerkat-stats="approved"]').text(data.counts.approved);
                $('[data-meerkat-stats="spam"]').text(data.counts.spam);

                if (typeof window.meerkatDashboard !== 'undefined') {
                    window.meerkatDashboard.data.datasets[0].data[0] = data.counts.spam;
                    window.meerkatDashboard.data.datasets[0].data[1] = data.counts.pending;
                    window.meerkatDashboard.data.datasets[0].data[2] = data.counts.approved;
                }

            });
        }, 30000); //30000);
    });

})();
//# sourceMappingURL=control-panel.js.map
