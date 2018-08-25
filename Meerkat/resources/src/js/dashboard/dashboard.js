(function() {
  var MetricPoints = Object.freeze({
    Approved: 2,
    Spam: 0,
    Pending: 1
  });

  var Endpoints = Object.freeze({
    Approved: cp_url("/addons/meerkat?filter=approved"),
    Spam: cp_url("/addons/meerkat?filter=spam"),
    Pending: cp_url("/addons/meerkat?filter=pending")
  });

  $(document).ready(function() {
    if ($("#meerkatDashboardChart").length) {
      //var ctx = document.getElementById("myChart").getContext('2d');
      window.meerkatDashboard = new Chart(
        document.getElementById("meerkatDashboardChart"),
        {
          type: "doughnut",
          data: {
            labels: [
              translate("addons.Meerkat::comments.metric_spam"),
              translate("addons.Meerkat::comments.metric_pending"),
              translate("addons.Meerkat::comments.metric_approved")
            ],
            datasets: [
              {
                label: translate("addons.Meerkat::comments.comments"),
                data: [
                  MeerkatCommentStats.Spam,
                  MeerkatCommentStats.Pending,
                  MeerkatCommentStats.Approved
                ],
                backgroundColor: [
                  "rgba(192, 57, 43,1.0)",
                  "rgba(142, 68, 173,1.0)",
                  "rgba(52, 73, 94,1.0)"
                ]
              }
            ]
          }
        }
      );

      meerkatDashboard.canvas.onclick = function(evt) {
        var activePoints = meerkatDashboard.getElementsAtEvent(evt);

        if (activePoints !== "undefined" && activePoints.length > 0) {
          var index = activePoints[0]._index;

          if (index == MetricPoints.Approved) {
            window.location = Endpoints.Approved;
          } else if (index == MetricPoints.Pending) {
            window.location = Endpoints.Pending;
          } else if (index == MetricPoints.Spam) {
            window.location = Endpoints.Spam;
          }
        }
      };
    }
  });
})();
