Meerkat.setDossierCellTemplate(`
    <a v-if="$index === 0" :href="item.edit_url">
        <span class="status status-{{ (item.published) ? 'live' : 'hidden' }}"
              :title="(item.published) ? translate('cp.published') : translate('cp.draft')"
        ></span>
        {{ item[column.label] }}
    </a>
    <template v-else>
        {{ item[column.label] }}
    </template>
`);