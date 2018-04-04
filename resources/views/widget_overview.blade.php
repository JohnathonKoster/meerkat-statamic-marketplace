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
        <div class="row">
            <div class="col-md-6">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <tbody>
                            <tr>
                                <td><a href="{{ route('cp') }}/addons/meerkat">{{ meerkat_trans('comments.comments') }}</a></td>
                                <td><a href="{{ route('cp') }}/addons/meerkat" data-meerkat-stats="all">{{ $stats['all'] }}</a></td>
                            </tr>
                            <tr>
                                <td><a style="color:rgba(52, 73, 94,1.0)" href="{{ route('cp') }}/addons/meerkat?filter=approved">{{ meerkat_trans('comments.metric_approved') }}</a></td>
                                <td><a style="color:rgba(52, 73, 94,1.0)" href="{{ route('cp') }}/addons/meerkat?filter=approved" data-meerkat-stats="approved">{{ $stats['approved'] }}</a></td>
                            </tr>
                            <tr>
                                <td><a style="color:rgba(142, 68, 173,1.0)" href="{{ route('cp') }}/addons/meerkat?filter=pending">{{ meerkat_trans('comments.metric_pending') }}</a></td>
                                <td><a style="color:rgba(142, 68, 173,1.0)" href="{{ route('cp') }}/addons/meerkat?filter=pending" data-meerkat-stats="pending">{{ $stats['pending'] }}</a></td>
                            </tr>
                            <tr>
                                <td><a style="color:rgba(192, 57, 43,1.0)" href="{{ route('cp') }}/addons/meerkat?filter=spam">{{ meerkat_trans('comments.metric_spam') }}</a></td>
                                <td><a style="color:rgba(192, 57, 43,1.0)" href="{{ route('cp') }}/addons/meerkat?filter=spam" data-meerkat-stats="spam">{{ $stats['spam'] }}</a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-md-6">
                <canvas id="meerkatDashboardChart" class="chartjs" width="undefined" height="undefined"></canvas>
            </div>
        </div>
    </div>
</div>
