<template>
  <b-container fluid>
    <b-row>
      <b-col>
        <h1>{{ $tc('véhicule', 2) | capitalize }}</h1>
      </b-col>
      <b-col class="admin__buttons">
        <b-btn v-if="creatable" :to="`/admin/${slug}/new`">
          {{ $t('créer un véhicule') | capitalize }}
        </b-btn>
      </b-col>
    </b-row>

    <b-row>
      <b-col class="admin__selection">
        <div v-if="selected.length > 0">
          {{ $tc(
            '{count} véhicule sélectionné',
            selected.length,
            { count: selected.length }
          ) }}
        </div>
      </b-col>

      <b-col class="admin__filters">
        <admin-filters entity="loanables" :filters="filters" :params="contextParams"
          @change="contextParams = $event" />
      </b-col>
    </b-row>

    <b-row>
      <b-col>
        <b-table
          striped hover :items="data"
          selectable select-mode="multi" @row-selected="rowSelected"
          :busy="loading" :fields="table" no-local-sorting
          :sort-by.sync="sortBy" :sort-desc.sync="sortDesc" no-sort-reset
          :show-empty="true" empty-text="Pas de véhicule">
          <template v-slot:cell(type)="row">
            {{ $t(`types.${row.item.type}`) | capitalize }}
          </template>
          <template v-slot:cell(owner.user.full_name)="row">
            <span v-if="row.item.owner">
              {{ row.item.owner.user.full_name }}
            </span>
          </template>
          <template v-slot:cell(actions)="row">
            <admin-list-actions :row="row" :slug="slug"
              @restore="restoreItemModal(row.item)"
              @destroy="destroyItemModal(row.item)" />
          </template>
        </b-table>
      </b-col>
    </b-row>

    <b-row>
      <b-col md="6">
        <b-button class="mr-3" variant="outline-primary" @click="exportCSV">
          Générer (CSV)
        </b-button>
        <a variant="outline-primary" :href="exportUrl"
          target="_blank" v-if="context.exportUrl" @click="resetExportUrl">
          Télécharger (CSV)
        </a>
      </b-col>

      <b-col md="6">
        <b-pagination align="right" v-model="contextParams.page"
          :total-rows="total" :per-page="contextParams.per_page" />

        <b-form inline class="text-right">
          <label for="per_page" class="ml-auto mr-1">Par page</label>&nbsp;
          <b-form-select id="per_page" name="per_page"
            :options="[10,20,50,100]" v-model="contextParams.per_page" />
        </b-form>
      </b-col>
    </b-row>
  </b-container>
</template>

<script>
import AdminFilters from '@/components/Admin/Filters.vue';
import AdminListActions from '@/components/Admin/ListActions.vue';

import DataRouteGuards from '@/mixins/DataRouteGuards';
import ListMixin from '@/mixins/ListMixin';
import locales from '@/locales';

export default {
  name: 'AdminLoanables',
  mixins: [DataRouteGuards, ListMixin],
  components: {
    AdminFilters,
    AdminListActions,
  },
  data() {
    return {
      table: [
        { key: 'id', label: 'ID', sortable: true },
        { key: 'name', label: 'Nom', sortable: true },
        { key: 'type', label: 'Type', sortable: false },
        { key: 'owner.user.full_name', label: 'Propriétaire', sortable: false },
        { key: 'actions', label: 'Actions', tdClass: 'table__cell__actions' },
      ],
    };
  },
  methods: {
    itemLabel(item) {
      return `${item.name} (${item.id})`;
    },
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.loanables,
        ...locales.en.forms,
      },
      fr: {
        ...locales.fr.loanables,
        ...locales.fr.forms,
      },
    },
  },
};
</script>

<style lang="scss">
</style>
