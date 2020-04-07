<template>
  <b-container fluid>
    <b-row>
      <b-col>
        <h1>{{ $tc('emprunt', 2) | capitalize }}</h1>
      </b-col>
      <b-col class="admin__buttons">
        <b-btn v-if="creatable" :to="`/admin/${slug}/new`">
          {{ $t('créer un emprunt') | capitalize }}
        </b-btn>
      </b-col>
    </b-row>

    <b-row>
      <b-col class="admin__selection">
        <div v-if="selected.length > 0">
          {{ $tc(
            '{count} emprunt sélectionné',
            selected.length,
            { count: selected.length }
          ) }}
        </div>
      </b-col>

      <b-col class="admin__filters">
        <admin-filters entity="loans" :filters="filters" :params="contextParams" />
      </b-col>
    </b-row>

    <b-row>
      <b-col>
        <b-table
          striped hover :items="data"
          selectable select-mode="multi" @row-selected="rowSelected"
          :busy="loading" :fields="table" no-local-sorting
          :sort-by.sync="sortBy" :sort-desc.sync="sortDesc" no-sort-reset
          :show-empty="true" empty-text="Pas d'emprunt">
          <template v-slot:cell(borrower.user.full_name)="row">
            {{ row.item.borrower.user.full_name }}
          </template>
          <template v-slot:cell(actions)="row">
            <div class="text-right">
              <b-button size="sm" variant="primary" :to="`/admin/${slug}/${row.item.id}`">
                {{ $t('afficher') | capitalize }}
              </b-button>
            </div>
          </template>
        </b-table>
      </b-col>
    </b-row>

    <b-row>
      <b-col>
        <b-pagination align="right" v-model="contextParams.page"
          :total-rows="total" :per-page="contextParams.per_page" />
      </b-col>
    </b-row>
  </b-container>
</template>

<script>
import AdminFilters from '@/components/Admin/Filters.vue';

import DataRouteGuards from '@/mixins/DataRouteGuards';
import ListMixin from '@/mixins/ListMixin';
import locales from '@/locales';

export default {
  name: 'AdminLoans',
  mixins: [DataRouteGuards, ListMixin],
  components: { AdminFilters },
  data() {
    return {
      table: [
        { key: 'id', label: 'ID', sortable: true },
        { key: 'borrower.user.full_name', label: 'Emprunteur', sortable: true },
        { key: 'loanable.owner.user.full_name', label: 'Propriétaire', sortable: true },
        { key: 'community.name', label: 'Communauté', sortable: true },
        { key: 'actions', label: 'Actions', tdClass: 'table__cell__actions' },
      ],
    };
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.loans,
        ...locales.en.forms,
      },
      fr: {
        ...locales.fr.loans,
        ...locales.fr.forms,
      },
    },
  },
};
</script>

<style lang="scss">
</style>
