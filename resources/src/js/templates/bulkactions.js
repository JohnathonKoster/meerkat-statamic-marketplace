Meerkat.setBulkActionsTemplate(`
<select class="meerkat-bulk-actions form-control" v-model="bulkAction">
    <option value="delete" selected>{{ translate_choice('addons.Meerkat::actions.bulk_delete', checkedItems.length) }} ({{ checkedItems.length }} {{ translate_choice('addons.Meerkat::comments.comments_c', checkedItems.length) }})</option>
    <option value="approve">{{ translate_choice('addons.Meerkat::actions.bulk_approve', checkedItems.length) }} ({{ checkedItems.length }} {{ translate_choice('addons.Meerkat::comments.comments_c', checkedItems.length) }})</option>
    <option value="unapprove">{{ translate_choice('addons.Meerkat::actions.bulk_unapprove', checkedItems.length) }} ({{ checkedItems.length }} {{ translate_choice('addons.Meerkat::comments.comments_c', checkedItems.length) }})</option>
    <option value="spam">{{ translate_choice('addons.Meerkat::actions.bulk_mark_spam', checkedItems.length) }} ({{ checkedItems.length }} {{ translate_choice('addons.Meerkat::comments.comments_c', checkedItems.length) }})</option>
    <option value="notspam">{{ translate_choice('addons.Meerkat::actions.bulk_mark_not_spam', checkedItems.length) }} ({{ checkedItems.length }} {{ translate_choice('addons.Meerkat::comments.comments_c', checkedItems.length) }})</option>
</select>
<a href="#" @click.prevent="call('applyBulkActions', bulkAction)" class="btn btn-default" v-if="$parent.applyingBulkActions == false"><span class="icon icon-tools"></span> {{ translate('addons.Meerkat::actions.apply') }}</a>
<span v-if="$parent.applyingBulkActions == true"><span class="icon icon-circular-graph animation-spin"></span> {{ translate('addons.Meerkat::actions.applying') }}</span>
`);