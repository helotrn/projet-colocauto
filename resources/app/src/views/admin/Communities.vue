<template>
  <b-container fluid>
    <b-row class="sm-stack">
      <b-col>
        <h1>{{ $tc("communauté", 2) | capitalize }}</h1>
      </b-col>
      <b-col class="admin__buttons">
        <b-btn v-if="creatable" :to="`/admin/${slug}/new`">
          {{ $t("créer une communauté") | capitalize }}
        </b-btn>
      </b-col>
    </b-row>

    <b-row>
      <b-col class="admin__selection">
        <div v-if="selected.length > 0">
          {{ $tc("{count} communauté sélectionnée", selected.length, { count: selected.length }) }}
        </div>
      </b-col>

      <b-col class="admin__filters">
        <admin-filters
          entity="communities"
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
          :fields="fields"
          no-local-sorting
          :sort-by.sync="sortBy"
          :sort-desc.sync="sortDesc"
          no-sort-reset
          :show-empty="true"
          empty-text="Pas de communauté"
        >
          <template v-slot:cell(type)="row">
            {{ $t(`fields.types.${row.item.type}`) | capitalize }}
          </template>
          <template v-slot:cell(parent.name)="row">
            {{ row.item.parent ? row.item.parent.name : "-" }}
          </template>
          <template v-slot:cell(admins.full_name)="{item}">
            <router-link v-for="admin in item.admins" :to="'/admin/users/'+admin.id" class="comma-list">
              {{ admin.full_name }}
            </router-link>
          </template>
          <template v-slot:cell(actions)="row">
            <admin-list-actions :columns="['edit']" :row="row" :slug="slug" />
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
  name: "AdminCommunities",
  mixins: [DataRouteGuards, ListMixin],
  components: {
    AdminFilters,
    AdminListActions,
    AdminPagination,
  },
  data() {
    return {
      selected: [],
      fields: [
        { key: "id", label: "ID", sortable: true, class: "text-right tabular-nums" },
        { key: "name", label: "Nom", sortable: true },
        { key: "type", label: "Type", sortable: true },
        { key: "admins.full_name", label: "Administrateur·ices", sortable: true },
        { key: "actions", label: "Actions", tdClass: "table__cell__actions" },
      ],
    };
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.communities,
        ...locales.en.forms,
      },
      fr: {
        ...locales.fr.communities,
        ...locales.fr.forms,
      },
    },
  },
};
</script>

<style lang="scss">
  .comma-list:after {
    content: ", ";
  }
  .comma-list:last-child:after {
    content: "";
  }
</style>
