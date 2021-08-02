<template>
  <b-container fluid>
    <b-row>
      <b-col>
        <h1>{{ $tc("cadenas", 2) | capitalize }}</h1>
      </b-col>
      <b-col class="admin__buttons">
        <b-btn v-if="creatable" :to="`/admin/${slug}/new`">
          {{ $t("créer un cadenas") | capitalize }}
        </b-btn>
      </b-col>
    </b-row>

    <b-row>
      <b-col class="admin__selection">
        <div v-if="selected.length > 0">
          {{ $tc("{count} cadenas sélectionné", selected.length, { count: selected.length }) }}
        </div>
      </b-col>

      <b-col class="admin__filters">
        <admin-filters
          entity="padlocks"
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
          empty-text="Pas de cadenas"
        >
          <template v-slot:cell(actions)="row">
            <admin-list-actions
              :columns="['edit', 'restore']"
              :row="row"
              :slug="slug"
              @restore="restoreItemModal(row.item)"
            />
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
  name: "AdminPadlocks",
  mixins: [DataRouteGuards, ListMixin],
  components: {
    AdminFilters,
    AdminListActions,
    AdminPagination,
  },
  data() {
    return {
      table: [
        { key: "external_id", label: "ID", sortable: true },
        { key: "name", label: "Nom", sortable: true },
        { key: "mac_address", label: "Adresse MAC", sortable: true },
        { key: "loanable.name", label: "Objet", sortable: true },
        { key: "actions", label: "Actions", tdClass: "table__cell__actions" },
      ],
    };
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.padlocks,
        ...locales.en.forms,
      },
      fr: {
        ...locales.fr.padlocks,
        ...locales.fr.forms,
      },
    },
  },
};
</script>

<style lang="scss"></style>
