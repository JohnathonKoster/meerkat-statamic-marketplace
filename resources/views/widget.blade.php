<script type="text/javascript">
    window.MeerkatCommentStats = {
        Approved: {{ $stats['approved'] }},
        Spam: {{ $stats['spam'] }},
        All: {{ $stats['all'] }},
        Pending: {{ $stats['pending'] }}
    };
</script>
<div class="card">
    <div class="head">
        <h1><a href="{{ '/' . CP_ROUTE . '/addons/meerkat' }}">{{ meerkat_trans('comments.comments') }}</a></h1>
        <form method="post" action="/!/Meerkat/checkForSpam">
            {{ csrf_field() }}
            <input type="hidden" name="redirect" value="cp" />
            <button class="btn btn-primary" type="submit">Check for Spam</button>
        </form>
    </div>
    <div class="card-body">
        <canvas id="meerkatDashboardChart" class="chartjs" width="undefined" height="undefined"></canvas>
    </div>
</div>
