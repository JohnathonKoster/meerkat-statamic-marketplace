<meerkat-conversation-view inline-template v-cloak>
    <div v-show="isOpen">
        <div data-meerkat-conversation-view="backdrop">
            <div data-meerkat-conversation-view="conversation">
                <div class="meerkat-conversation-header" v-if="rootComment != null">
                    <a v-on:click="close()" class="pull-right"><span class="icon icon-cross"></span></a>
                    <h3>{{ meerkat_trans('comments.conversation') }} @{{ rootComment.name }}</h3>
                </div>
                <div class="meerkat-conversation-body">
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
                        perpage="10"
                        ifilter="all"
                        hidemanagement="true"
                        autoload="false">
                    <dossier-table v-if="hasItems" :options="tableOptions"></dossier-table>
                </meerkat-stream-listing>
                </div>
            </div>
        </div>
    </div>
</meerkat-conversation-view>