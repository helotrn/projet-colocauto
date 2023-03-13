<template>
  <b-container fluid>
    <b-row>
      <b-col>
        <h1>{{ $tc("model_name", 2) | capitalize }}</h1>
      </b-col>
      <b-col class="admin__buttons">
        <b-btn v-if="creatable" :to="`/admin/${slug}/new`">
          {{ $t("list.create") | capitalize }}
        </b-btn>
      </b-col>
    </b-row>

    <b-row>
      <b-col class="admin__selection">
        <div v-if="selected.length > 0">
          {{ $tc("list.selected", selected.length, { count: selected.length }) }}
        </div>
      </b-col>

      <b-col class="admin__filters">
        <admin-filters
          entity="expense_tags"
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
          :busy="loading"
          :fields="table"
          no-local-sorting
          :sort-by.sync="sortBy"
          :sort-desc.sync="sortDesc"
          no-sort-reset
          :show-empty="true"
          empty-text="Pas de mot-clé"
        >
          <template v-slot:cell(color)="row">
            <span class="badge" :class="`badge-${row.value}`">{{ colorNames[row.value] }}</span>
          </template>
          <template v-slot:cell(actions)="row">
            <admin-list-actions :columns="['edit']" :row="row" :slug="slug" />
          </template>
        </b-table>
      </b-col>
    </b-row>

    <b-row>
      <b-col md="6" />

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
  name: "AdminExpenseTags",
  mixins: [DataRouteGuards, ListMixin],
  components: {
    AdminFilters,
    AdminListActions,
    AdminPagination,
  },
  data() {
    return {
      table: [
        { key: "id", label: "ID", sortable: true, class: "text-right tabular-nums" },
        { key: "name", label: "Nom", sortable: true },
        { key: "color", label: "Couleur", sortable: true },
        { key: "slug", label: "Nom système", sortable: true },
        { key: "actions", label: "Actions", tdClass: "table__cell__actions" },
      ],
      colorNames: {
        primary: 'Bleu',
        success: 'Vert',
        danger: 'Rouge',
        secondary: 'Gris',
        dark: 'Noir',
      }
    };
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.expense_tags,
        ...locales.en.forms,
      },
      fr: {
        ...locales.fr.expense_tags,
        ...locales.fr.forms,
      },
    },
  },
};
</script>

<style lang="scss"></style>
