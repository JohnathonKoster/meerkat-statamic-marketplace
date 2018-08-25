Meerkat.setMeerkatCellTemplate(`
<div v-if="sizes.md || sizes.lg">
<div class="media" v-if="$index === 0">
    <partial name="avatar"></partial>
    <div class="media-body"> <h4 class="media-heading">{{ item['name'] }}</h4>
        <span class="icon icon-mail"></span> <a href="mailto:{{ item['email'] }}">{{ item['email'] }}</a><br />
        
        <div v-if="item['url']"><a href="{{ item['url'] }}" target="_blank"><span class="icon icon-globe"></span> {{ item['url'] }}</a></div>
    </div>
</div>
<div v-if="$index === 1">
    <div data-meerkat-comment="response" v-if="item['is_reply']">
    {{ translate('addons.Meerkat::comments.in_reply_to_simple') }} <a @click.prevent="call('openConversation', item['parent_comment_id'])">{{ item['parent_comment_name'] }}</a>
    </div>

    <div data-meerkat-comment="content" v-if="item['editing'] === false">
    {{{ item['comment'] }}}
    </div>
    <div v-if="item['editing'] === true">
        <div class="markdown-fieldtype">
            <markdown-fieldtype :data.sync="item['original_markdown']"></markdown-fieldtype>
        </div>
        <ul class="list-inline" data-meerkat-has="actions">
        <li v-if="item['saving'] === false"><a href="#" @click.prevent="call('saveItemEdits', item.id)"><span class="icon icon-check"></span> {{ translate('addons.Meerkat::actions.save') }}</a></li>
        <li v-if="item['saving'] === false"><a href="#" @click.prevent="call('cancelItemEdit', item.id)">{{ translate('addons.Meerkat::actions.cancel') }}</a></li>
        <li v-if="item['saving'] === true"><span class="icon icon-circular-graph animation-spin"></span> {{ translate('addons.Meerkat::actions.saving') }}</li>
        </ul>
    </div>
    <div v-if="item['writing_reply'] === true">
        <div class="markdown-fieldtype">
            <markdown-fieldtype :data.sync="item['new_reply']"></markdown-fieldtype>
        </div>
        <ul class="list-inline" data-meerkat-has="actions">
        <li v-if="item['saving'] === false"><a href="#" @click.prevent="call('createNewReply', item.id)"><span class="icon icon-reply"></span> {{ translate('addons.Meerkat::actions.reply') }}</a></li>
        <li v-if="item['saving'] === false"><a href="#" @click.prevent="call('cancelPostReply', item.id)">{{ translate('addons.Meerkat::actions.cancel') }}</a></li>
        <li v-if="item['saving'] === true"><span class="icon icon-circular-graph animation-spin"></span> {{ translate('addons.Meerkat::actions.replying') }}</li>
        </ul>
    </div>
    <div data-meerkat-type="comment" data-meerkat-has="actions"
         v-if="hasActions">
    <ul class="list-inline" v-if="item['editing'] === false && item['writing_reply'] === false">
    <partial name="actions"></partial>
    </ul>
    </div>
</div>
<div v-if="$index === 3">
    <a href="/cp/collections/entries{{ item['in_response_to_edit_url'] }}">{{ item['in_response_to'] }}</a><br />
    <div v-if="item['published']">
        <small><a href="{{ item['in_response_to_url'] }}#comment-{{ item['id'] }}" target="_blank" title="{{ translate('addons.Meerkat::actions.view_post_desc') }}">{{ translate('addons.Meerkat::actions.view_post') }}</a></small><br />
    </div>
    <span class="label label-default"><span class="icon icon-chat"></span> {{ item['comment_count'] }}</span>
</div>
</div>
<div v-if="sizes.sm || sizes.xs">
    <div class="meerkat-mobile-card" id="meerkat-mobile-comment-card-{{ item['id'] }}" v-bind:class="{ 'meerkat-conversation-intended-comment': ($parent.$parent.intendedComment != null && $parent.$parent.intendedComment == item['id']) }">
        <div class="meerkat-mobile-card-header" v-bind:class="{ 'is-selected': item.checked }">
            <div class="meerkat-mobile-card-avatar">
                <partial name="avatar"></partial>
            </div>
            <a class="meerkat-mobile-card-indicator" v-bind:class="{ 'checked': item.checked }" @click.prevent="$parent.toggle(item)"></a>
            <h5>
                {{ item['name'] }}
                <span v-if="item['is_reply']">
                    <span v-if="$parent.$parent.loadStreamFor !== null">
                        - {{ translate('addons.Meerkat::comments.in_reply_to_simple') }} {{ item['parent_comment_name'] }}
                    </span>
                    <span v-else>
                        - {{ translate('addons.Meerkat::comments.in_reply_to_simple') }} <a @click.prevent="call('openConversation', item['parent_comment_id'])">{{ item['parent_comment_name'] }}
                        <span v-if="item['conversation_participants'].length - 2 > 0">( +{{ item['conversation_participants'].length - 2 }} {{ Meerkat.getConversationLabel([]) }}
                            <span v-if="item['conversation_participants'].length - 2 == 1">{{ translate('addons.Meerkat::comments.conversation_other_singular') }}</span>
                            <span v-if="item['conversation_participants'].length - 2 > 1">{{ translate('addons.Meerkat::comments.conversation_other_plural') }}</span>
                        )</span></a>
                    </span>
                </span>
            </h5>
            <h6><a href="mailto:{{ item['email'] }}">{{ item['email'] }}</a></h6>
        </div>
        <div class="meerkat-mobile-card-content">
            <div class="comment-header-options meerkat-mobile-card-reply-to">
                {{{ item['in_response_string'] }}}
            </div>

            <div data-meerkat-comment="content" v-if="item['editing'] === false">
            {{{ item['comment'] }}}
            </div>
            <div v-if="item['editing'] === true">
                <div class="markdown-fieldtype">
                    <markdown-fieldtype :data.sync="item['original_markdown']"></markdown-fieldtype>
                </div>
                <ul class="list-inline meerkat-mobile-edit-actions" data-meerkat-has="actions">
                <li v-if="item['saving'] === false"><a href="#" @click.prevent="call('saveItemEdits', item.id)"><span class="icon icon-check"></span> {{ translate('addons.Meerkat::actions.save') }}</a></li>
                <li v-if="item['saving'] === false"><a href="#" @click.prevent="call('cancelItemEdit', item.id)">{{ translate('addons.Meerkat::actions.cancel') }}</a></li>
                <li v-if="item['saving'] === true"><span class="icon icon-circular-graph animation-spin"></span> {{ translate('addons.Meerkat::actions.saving') }}</li>
                </ul>
            </div>
            <div v-if="item['writing_reply'] === true">
                <div class="markdown-fieldtype">
                    <markdown-fieldtype :data.sync="item['new_reply']"></markdown-fieldtype>
                </div>
                <ul class="list-inline meerkat-mobile-edit-actions" data-meerkat-has="actions">
                <li v-if="item['saving'] === false"><a href="#" @click.prevent="call('createNewReply', item.id)"><span class="icon icon-reply"></span> {{ translate('addons.Meerkat::actions.reply') }}</a></li>
                <li v-if="item['saving'] === false"><a href="#" @click.prevent="call('cancelPostReply', item.id)">{{ translate('addons.Meerkat::actions.cancel') }}</a></li>
                <li v-if="item['saving'] === true"><span class="icon icon-circular-graph animation-spin"></span> {{ translate('addons.Meerkat::actions.replying') }}</li>
                </ul>
            </div>
        </div>
        <div class="meerkat-mobile-card-footer" v-if="item['editing'] === false && item['writing_reply'] === false">
            <div data-meerkat-type="comment" data-meerkat-has="actions"
                    v-if="hasActions">
                <ul class="list-inline meerkat-mobile-card-actions" v-if="item['editing'] === false && item['writing_reply'] === false">
                <partial name="actions"></partial>
                </ul>
            </div>
        </div>
    </div>
</div>
`);