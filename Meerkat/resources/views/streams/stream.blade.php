@include('Meerkat::streams/conversation')

<meerkat-stream-listing inline-template v-cloak
                        get="/!/Meerkat/api-comments"
                        create="{{ route('comments.create') }}"
                        delete="{{ route('comments.delete') }}"
                        spam="{{ route('comments.spam') }}"
                        notspam="{{ route('comments.notspam') }}"
                        approve="{{ route('comments.approve') }}"
                        unapprove="{{ route('comments.unapprove') }}"
                        update="{{ route('comments.update') }}"
                        checkspam="{{ route('comments.checkspam') }}"
                        getcounts="{{ route('comments.counts') }}"
                        perpage="10"
                        ifilter="{{ $filter }}"
                        hidemanagement="{{ $hideManagement }}"
                        autoload="true">

    <div class="form-submission-listing">

        <div class="card flat-bottom" v-if="hideManagement == false">
            <div class="head">

                <h1 data-meerkat-ui="comments-header">{{ meerkat_trans('comments.comments') }}</h1>

                @can('super')
                    <a href="{{ route('form.edit', ['form' => 'Meerkat']) }}" class="btn">{{ meerkat_trans('actions.configure') }}</a>
                @endcan

                <a href="#" @click.prevent="checkForSpam" class="btn"><span v-if="checkingSpam == true"><span class="icon icon-circular-graph animation-spin"></span></span> {{ meerkat_trans('actions.check_for_spam') }}</a>

                <div class="btn-group">
                    <a href="{{ route('comments.export', ['type' => 'csv']) }}&download=true"  class="btn btn-default">{{ meerkat_trans('actions.export') }}</a>
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a href="{{ meerkat_cppath() }}forms/contact/export/csv?download=true">{{ meerkat_trans('actions.export_csv') }}</a></li>
                        <li><a href="{{ meerkat_cppath() }}forms/contact/export/json?download=true">{{ meerkat_trans('actions.export_json') }}</a></li>
                    </ul>
                </div>
            </div>
        </div>

        @if (isset($form) && ! empty($form->metrics()))
            <div class="card flat-top">
                <div class="metrics">
                    @foreach($form->metrics() as $metric)
                        <div class="metric simple">
                            <div class="count">
                                <small>{{ $metric->label() }}</small>
                                <h2>{{ $metric->result() }}</h2>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="card flat-top meerkat-listing-card" v-if="loading == false">
            <div class="dossier-table-wrapper meerkat-table-wrapper">
                <div class="filter-wrapper">
                    <ul class="list-unstyled list-inline meerkat-filter" v-bind:class="{ 'pull-right': ($parent.sizes.lg || $parent.sizes.md) }">
                        <li><span class="icon icon-funnel"></span></li>
                        <li><a href="#" v-bind:class="{ 'active': filter === 'all' }" @click.prevent="filterItems('all')">{{ translate('addons.Meerkat::comments.metric_all') }} <span>(@{{ metrics.all }})</span></a></li>
                        <li><a href="#" v-bind:class="{ 'active': filter === 'approved' }" @click.prevent="filterItems('approved')">{{ translate('addons.Meerkat::comments.metric_approved') }} <span>(@{{ metrics.approved }})</span></a></li>
                        <li><a href="#" v-bind:class="{ 'active': filter === 'pending' }" @click.prevent="filterItems('pending')">{{ translate('addons.Meerkat::comments.metric_pending') }} <span>(@{{ metrics.pending }})</span></a></li>
                        <li><a href="#" v-bind:class="{ 'active': filter === 'spam' }" @click.prevent="filterItems('spam')">{{ translate('addons.Meerkat::comments.metric_spam') }} <span>(@{{ metrics.spam }})</span></a></li>
                    </ul>
                </div>
                <dossier-table v-if="hasItems" :options="tableOptions"></dossier-table>
            </div>
        </div>

        <div class="card flat-top" v-if="loading">
            <div class="loading">
                <span class="icon icon-circular-graph animation-spin"></span> {{ meerkat_trans('actions.loading') }}
            </div>
        </div>

        <div class="card" v-if="noItems">
            <div class="no-results">
                <span class="icon icon-chat"></span>
                @if (mb_strtolower(Input::get('filter', '')) == 'pending')
                    <h2 data-meerkat-ui="comments-body-header">{{ meerkat_trans('comments.comments_pending_possessive') }}</h2>
                    <h3 data-meerkat-ui="comments-body-subheader">{{ meerkat_trans('comments.no_pending_comments') }}</h3>
                @elseif (mb_strtolower(Input::get('filter', '')) == 'spam')
                    <h2 data-meerkat-ui="comments-body-header">{{ meerkat_trans('comments.comments_spam_possessive') }}</h2>
                    <h3 data-meerkat-ui="comments-body-subheader">{{ meerkat_trans('comments.no_spam_comments') }}</h3>
                @elseif (mb_strtolower(Input::get('filter', '')) == 'approved')
                    <h2 data-meerkat-ui="comments-body-header">{{ meerkat_trans('comments.comments_approved_possessive') }}</h2>
                    <h3 data-meerkat-ui="comments-body-subheader">{{ meerkat_trans('comments.no_approved_comments') }}</h3>
                @else
                    <h2 data-meerkat-ui="comments-body-header">{{ meerkat_trans('comments.comments_possessive') }}</h2>
                    <h3 data-meerkat-ui="comments-body-subheader">{{ meerkat_trans('comments.no_comments') }}</h3>
                @endif
                <h4><a href="#" @click.prevent="refreshView(null)">{{ meerkat_trans('actions.refresh_comments') }}</a></h4>
            </div>
        </div>

    </div>

</meerkat-stream-listing>