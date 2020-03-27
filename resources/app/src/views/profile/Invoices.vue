<template>
  <div class="profile-invoices" v-if="routeDataLoaded">
    <b-row>
      <b-col>
        <b-table
          striped hover :items="data"
          selectable select-mode="single" @row-selected="showInvoice"
          :busy="loading" :fields="fields" no-local-sorting
          :sort-by.sync="sortBy" :sort-desc.sync="sortDesc" no-sort-reset
          :show-empty="true" empty-text="Pas de facture">
          <template v-slot:cell(paid_at)="row">
            {{ row.item.paid_at ? '✓' : '✗' }}
          </template>
          <template v-slot:cell(created_at)="row">
            {{ row.item.created_at | date }}
          </template>
          <template v-slot:cell(total)="row">
            {{ row.item.total | currency }}
          </template>
          <template v-slot:cell(total_with_taxes)="row">
            {{ row.item.total_with_taxes | currency }}
          </template>
        </b-table>
      </b-col>
    </b-row>
  </div>
  <layout-loading v-else />
</template>

<script>
import Authenticated from '@/mixins/Authenticated';
import DataRouteGuards from '@/mixins/DataRouteGuards';
import ListMixin from '@/mixins/ListMixin';

import locales from '@/locales';

export default {
  name: 'ProfileInvoices',
  mixins: [Authenticated, DataRouteGuards, ListMixin],
  data() {
    return {
      fields: [
        { key: 'created_at', label: 'Date', sortable: true },
        { key: 'paid_at', label: 'Payée', sortable: true },
        {
          key: 'total',
          label: 'Total',
          sortable: true,
          tdClass: 'text-right',
        },
        {
          key: 'total_with_taxes',
          label: 'Total avec taxes',
          sortable: true,
          tdClass: 'text-right',
        },
      ],
    };
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.invoices,
        ...locales.en.forms,
      },
      fr: {
        ...locales.fr.invoices,
        ...locales.fr.forms,
      },
    },
  },
  methods: {
    showInvoice(row) {
      this.$router.push(`/profile/invoices/${row[0].id}`);
    },
  },
};
</script>

<style lang="scss">
.profile-invoices__invoices__invoice {
  margin-bottom: 20px;
}
</style>
