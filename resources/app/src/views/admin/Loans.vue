<template>
  <b-container fluid>
    <b-row class="sm-stack">
      <b-col>
        <h1>{{ $tc("model_name", 2) | capitalize }}</h1>
      </b-col>
      <b-col class="admin__buttons">
        <b-btn v-if="creatable" :to="`/admin/${slug}/new`">
          {{ $t("créer un emprunt") | capitalize }}
        </b-btn>
      </b-col>
    </b-row>

    <b-row>
      <b-col class="admin__selection">
        <div v-if="selected.length > 0">
          {{ $tc("{count} emprunt sélectionné", selected.length, { count: selected.length }) }}
        </div>
      </b-col>

      <b-col class="admin__filters">
        <admin-filters
          entity="loans"
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
          empty-text="Pas d'emprunt"
        >
          <template v-slot:cell(borrower.user.full_name)="row">
            {{ row.item.borrower.user.full_name }}
          </template>
          <template v-slot:cell(departure_at)="row">
            {{ $dayjs(row.item.departure_at).format("YYYY-MM-DD HH:mm") }}
          </template>
          <template v-slot:cell(actions)="row">
            <admin-list-actions :columns="['view']" :row="row" :slug="slug" />
          </template>
          <template v-slot:cell(status)="row">
            <loan-status :item="row.item"></loan-status>
          </template>
        </b-table>
      </b-col>
    </b-row>

    <b-row>
      <b-col md="6">
        <b-button class="mr-3" variant="outline-primary" @click="exportCSV">
          <b-spinner small v-if="generatingCSV"></b-spinner>
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
import LoanStatus from "@/components/Loan/Status.vue";
import AdminListActions from "@/components/Admin/ListActions.vue";
import AdminPagination from "@/components/Admin/Pagination.vue";

import DataRouteGuards from "@/mixins/DataRouteGuards";
import ListMixin from "@/mixins/ListMixin";
import locales from "@/locales";

export default {
  name: "AdminLoans",
  mixins: [DataRouteGuards, ListMixin],
  components: {
    AdminFilters,
    AdminListActions,
    AdminPagination,
    LoanStatus,
  },
  data() {
    return {
      table: [
        { key: "id", label: "ID", sortable: true, class: "text-right tabular-nums" },
        { key: "departure_at", label: "Départ", sortable: true, class: "tabular-nums" },
        { key: "borrower.user.full_name", label: "Emprunteur", sortable: true },
        { key: "loanable.owner.user.full_name", label: "Propriétaire", sortable: true },
        { key: "community.name", label: "Communauté", sortable: true },
        { key: "status", label: "Statut", sortable: false },
        { key: "final_distance", label: "Km parcourus", sortable: true},
        { key: "actions", label: "Actions", tdClass: "table__cell__actions" },
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

<style lang="scss" scoped>
  .btn .spinner-border {
    vertical-align: top;
  }
</style>
