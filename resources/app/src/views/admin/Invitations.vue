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
          entity="tags"
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
          no-sort-reset
          :show-empty="true"
          empty-text="Pas d'invitation"
        >
          <template v-slot:cell(actions)="row">
            <admin-list-actions :columns="['edit']" :row="row" :slug="slug" />
          </template>
          <template v-slot:cell(status)="row">
            <div class="status-container" v-if="row.item.consumed_at">
              <div v-b-tooltip.hover
                :title="row.item.consumed_at | date">
                <span class="loan-status-pill success">Utilisée</span>
              </div>
            </div>
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
  name: "AdminInvitations",
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
        { key: "actions", label: "Actions", tdClass: "table__cell__actions" },
      ],
      table: [
        { key: "id", label: "ID", sortable: true, class: "text-right tabular-nums" },
        { key: "email", label: "Email", sortable: true },
        { key: "community.name", label: "Communauté", sortable: true },
        { key: "token", label: "Code", sortable: false },
        { key: "status", label: "Statut", sortable: false },
        { key: "actions", label: "Actions", tdClass: "table__cell__actions" },
      ],
    };
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.invitations,
        ...locales.en.forms,
      },
      fr: {
        ...locales.fr.invitations,
        ...locales.fr.forms,
      },
    },
  },
};
</script>

<style lang="scss"></style>
