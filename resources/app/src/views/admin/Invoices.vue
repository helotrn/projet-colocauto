<template>
  <b-container fluid>
    <b-row>
      <b-col>
        <h1>{{ $tc("facture", 2) | capitalize }}</h1>
      </b-col>
      <b-col class="admin__buttons">
        <b-btn v-if="creatable" :to="`/admin/${slug}/new`">
          {{ $t("créer une facture") | capitalize }}
        </b-btn>
      </b-col>
    </b-row>

    <b-row>
      <b-col class="admin__selection">
        <div v-if="selected.length > 0">
          {{ $tc("{count} facture sélectionnée", selected.length, { count: selected.length }) }}
        </div>
      </b-col>

      <b-col class="admin__filters">
        <admin-filters
          entity="invoices"
          :filters="filters"
          :params="contextParams"
          @change="setParam"
        />
      </b-col>
    </b-row>

    <b-row>
      <b-col>
        <b-table
          striped
          hover
          :items="data"
          selectable
          select-mode="multi"
          @row-selected="rowSelected"
          :busy="loading"
          :fields="table"
          no-local-sorting
          :sort-by.sync="sortBy"
          :sort-desc.sync="sortDesc"
          no-sort-reset
          :show-empty="true"
          empty-text="Pas de facture"
        >
          <template v-slot:cell(user.full_name)="row">
            {{ row.item.user.full_name }}
          </template>
          <template v-slot:cell(paid_at)="row">
            {{ row.item.paid_at ? "✓" : "✗" }}
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
          <template v-slot:cell(actions)="row">
            <admin-list-actions :columns="['view']" :row="row" :slug="slug" />
          </template>
        </b-table>
      </b-col>
    </b-row>

    <b-row>
      <b-col md="6">
        <b-button class="mr-3" variant="outline-primary" @click="exportCSV">
          Générer (CSV)
        </b-button>
        <a
          variant="outline-primary"
          :href="exportUrl"
          target="_blank"
          v-if="context.exportUrl"
          @click="resetExportUrl"
        >
          Télécharger (CSV)
        </a>
      </b-col>

      <b-col md="6">
        <admin-pagination :params="contextParams" :total="total" @change="setParam" />
      </b-col>
    </b-row>
  </b-container>
</template>

<script>
import AdminFilters from "@/components/Admin/Filters.vue";
import AdminListActions from "@/components/Admin/ListActions.vue";
import AdminPagination from "@/components/Admin/Pagination.vue";

import DataRouteGuards from "@/mixins/DataRouteGuards";
import ListMixin from "@/mixins/ListMixin";
import locales from "@/locales";

export default {
  name: "AdminInvoices",
  mixins: [DataRouteGuards, ListMixin],
  components: {
    AdminListActions,
    AdminFilters,
    AdminPagination,
  },
  data() {
    return {
      table: [
        { key: "user.full_name", label: "Membre", sortable: true },
        { key: "created_at", label: "Date", sortable: true },
        { key: "paid_at", label: "Payée", sortable: true },
        {
          key: "total",
          label: "Total",
          sortable: true,
          tdClass: "text-right tabular-nums",
        },
        {
          key: "total_with_taxes",
          label: "Total avec taxes",
          sortable: true,
          tdClass: "text-right tabular-nums",
        },
        { key: "actions", label: "Actions", tdClass: "table__cell__actions" },
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
};
</script>

<style lang="scss"></style>
