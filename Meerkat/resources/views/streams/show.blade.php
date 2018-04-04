@extends('layout')

@section('content')
    <form-submission-listing inline-template v-cloak
                             get="/!/Meerkat/api-stream-comments?context={{ $stream['context_id'] }}">

        <div class="form-submission-listing">

            <div class="card flat-bottom">
                <div class="head">
                    <h1>{{ str_limit($stream['context'], 50) }} Comments</h1>

                    <a href="{{ $context->url() }}#discussion" target="_blank" class="btn">View Page</a>
                </div>
            </div>

            <div class="card flat-top">
                @if (! empty($form->metrics()))
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
                @endif
            </div>

            <div class="card" v-if="noItems">
                <div class="no-results">
                    <span class="icon icon-chat"></span>
                    <h2>Comment Streams</h2>
                    <h3>{{ trans('cp.empty_responses') }}</h3>
                </div>
            </div>

            <template v-else>
                <div class="card flat-bottom">
                    <h1>Comments</h1>
                </div>
                <div class="card flat-top">
                    <dossier-table v-if="hasItems" :options="tableOptions"></dossier-table>
                </div>
            </template>
        </div>
    </form-submission-listing>

@endsection