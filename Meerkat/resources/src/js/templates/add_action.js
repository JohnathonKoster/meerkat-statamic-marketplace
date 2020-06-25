Meerkat.setMeerkatAddActionPartialTemplate(`
    <li v-if="item['published'] === false && item['is_approving'] === false"><a href="#" @click.prevent="call('approveComment', item)" title="{{ translate('addons.Meerkat::actions.approve_desc') }}"><span class="icon icon-check"></span> {{ translate('addons.Meerkat::actions.approve') }}</a></li>
    <li v-if="item['published'] === false && item['is_approving'] === true"><a href="#"><span class="icon icon-circular-graph animation-spin"></span> {{ translate('addons.Meerkat::actions.approving') }}</a></li>
    
    <li v-if="item['published'] === true && item['is_taking_action'] == false"><a href="#" @click.prevent="call('unApproveComment', item)" title="{{ translate('addons.Meerkat::actions.unapprove_desc') }}">{{ translate('addons.Meerkat::actions.unapprove') }}</a></li>
    <li v-if="item['published'] === true && item['is_unapproving'] === true"><a href="#"><span class="icon icon-circular-graph animation-spin"></span> {{ translate('addons.Meerkat::actions.unapproving') }}</a></li>
    
    
    <li v-if="item['is_taking_action'] == false"><a href="#" @click.prevent="call('replyToComment', item)" title="{{ translate('addons.Meerkat::actions.reply_desc') }}"><span class="icon icon-reply"></span> {{ translate('addons.Meerkat::actions.reply') }}</a></li>
    <li v-if="item['is_taking_action'] == false"><a href="#" @click.prevent="call('editComment', item)" title="{{ translate('addons.Meerkat::actions.edit_desc') }}"><span class="icon icon-edit"></span> {{ translate('addons.Meerkat::actions.edit') }}</a></li>

    <li v-if="item['spam'] === false && item['is_taking_action'] == false"><a href="#" @click.prevent="call('markItemAsSpam', item)" title="{{ translate('addons.Meerkat::actions.spam_desc') }}"><span class="icon icon-shield"></span> {{ translate('addons.Meerkat::actions.spam') }}</a></li>
    <li v-if="item['spam'] === false && item['is_markingspam'] == true"><a href="#"><span class="icon icon-circular-graph animation-spin"></span> {{ translate('addons.Meerkat::actions.spam_submitting') }}</a></li>

    <li v-if="item['spam'] === true && item['is_taking_action'] == false"><a href="#" @click.prevent="call('markItemAsNotSpam', item)" title="{{ translate('addons.Meerkat::actions.not_spam_desc') }}"><span class="icon icon-shield"></span> {{ translate('addons.Meerkat::actions.not_spam') }}</a></li>
    <li v-if="item['spam'] === true && item['is_markingnotspam'] == true"><a href="#"><span class="icon icon-circular-graph animation-spin"></span> {{ translate('addons.Meerkat::actions.spam_submitting') }}</a></li>

    <li v-if="item['is_taking_action'] == false"><a href="#" @click.prevent="call('deleteItem', item)" title="{{ translate('addons.Meerkat::actions.delete_desc') }}"><span class="icon icon-trash"></span> {{ translate('addons.Meerkat::actions.delete') }}</a></li>
    <li v-if="item['is_deleting'] === true"><a href="#"><span class="icon icon-circular-graph animation-spin"></span> {{ translate('addons.Meerkat::actions.delete_removing') }}</a></li>
`);