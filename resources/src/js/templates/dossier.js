Meerkat.setDossierTemplate(`
<div class="meerkat-bulk-action-wrapper pull-left" v-if="showBulkActions">
    <partial name="bulkActions"></partial>
</div>

<table class="dossier meerkat-comments-table table-striped" v-if="sizes.md || sizes.lg">
    <thead v-if="hasHeaders">
        <tr>
            <th class="checkbox-col" v-if="hasCheckboxes">
                <input type="checkbox" id="checkbox-all" :checked="allItemsChecked" @click="checkAllItems" />
                <label for="checkbox-all"></label>
            </th>

            <th v-for="column in columns"
                @click="sortBy(column)"
                class="column-sortable"
                :class="['column-' + column.label, {'active': sortCol === column.field} ]"
            >
                <template v-if="column.translation">{{ column.translation }}</template>
                <template v-else>{{ translate('cp.'+column.label) }}</template>
                <i v-if="sortCol === column.field"
                    class="icon icon-chevron-{{ (sortOrders[column.field] > 0) ? 'up' : 'down' }}"></i>
            </th>
        </tr>
    </thead>
    <tbody data-meerkat-type="comment" v-el:tbody v-for="item in items | filterBy computedSearch | caseInsensitiveOrderBy computedSortCol computedSortOrder">
        <tr
            data-meerkat-type="comment"
            data-meerkat-comment-id="{{ item['id'] }}" data-meerkat-comment-published="{{ item['published'].toString() }}"
            data-meerkat-comment-spam="{{ item['spam'].toString() }}">
        <td colspan="3">
        <div class="comment-header-options" v-if="item['published']"><a href="{{ item['in_response_to_url'] }}#comment-{{ item['id'] }}" target="_blank" title="{{ translate('addons.Meerkat::actions.view_post_desc') }}">{{ translate('addons.Meerkat::actions.view_post') }}</a></div>
        <div class="float-left"><a name="meerkat-comment-{{ item['id'] }}"></a><span class="icon icon-flag" v-if="item['published'] === false"></span> {{{ item['in_response_string'] }}}</div>
        </td>
        </tr>
        <tr data-meerkat-type="comment"
            data-meerkat-comment-id="{{ item['id'] }}" data-meerkat-comment-published="{{ item['published'].toString() }}"
            data-meerkat-comment-spam="{{ item['spam'].toString() }}">

            <td class="checkbox-col" v-if="hasCheckboxes && !reordering">
                <input type="checkbox" :id="'checkbox-' + $index" :checked="item.checked" @change="toggle(item)" />
                <label :for="'checkbox-' + $index"></label>
            </td>

            <td class="checkbox-col" v-if="reordering">
                <div class="drag-handle">
                    <i class="icon icon-menu"></i>
                </div>
            </td>

            <td v-for="column in columns" class="cell-{{ column.field }}">
                <partial name="cell"></partial>
            </td>
        </tr>
    </tbody>
</table>
<div v-if="sizes.sm || sizes.xs" class="meerkat-mobile-table">
    <div v-for="item in items | filterBy computedSearch | caseInsensitiveOrderBy computedSortCol computedSortOrder">
        <div data-meerkat-mobile="wrap" data-meerkat-type="comment"
            data-meerkat-comment-id="{{ item['id'] }}" data-meerkat-comment-published="{{ item['published'].toString() }}"
            data-meerkat-comment-spam="{{ item['spam'].toString() }}">
            <partial name="cell"></partial>                        
        </div>
    </div>
</div>
<div class="meerkat-pagination-wrapper">
    <ul class="pagination meerkat-pagination">
        <li v-if="$parent.pagination.prevPage">
            <a href="" @click.prevent="call('previousPage')" aria-label="{{ translate('addons.Meerkat::pagination.previous') }}"><span>&laquo;</span></a>
        </li>
        <li v-for="page in $parent.pages">
            <a href="" @click.prevent="call('goToPage', page.page)" v-bind:class="{ 'active': page.active }" :disabled="page.page === null">{{ page.name }}</a>
        </li>
        <li v-if="$parent.pagination.nextPage">
            <a href="" @click.prevent="call('nextPage')" aria-label="{{ translate('addons.Meerkat::pagination.next') }}"><span>&raquo;</span></a>
        </li>
    </ul>
</div>
`);