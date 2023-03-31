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
          entity="expenses"
          :filters="filters"
          :params="contextParams"
          @change="setParam"
        />
      </b-col>
    </b-row>

    <b-row>
      <b-col>
        <expenses-table-list
          :visibleFields="[
            'id',
            'name',
            'amount',
            'user.full_name',
            'loanable.name',
            'executed_at',
            'tag',
            'changes',
            'actions'
          ]"
          :items="data"
          :busy="loading"
        >
        </expenses-table-list>
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
import ExpensesTableList from "@/components/Expenses/TableList.vue";

import DataRouteGuards from "@/mixins/DataRouteGuards";
import ListMixin from "@/mixins/ListMixin";
import locales from "@/locales";

export default {
  name: "AdminExpenses",
  mixins: [DataRouteGuards, ListMixin],
  components: {
    AdminFilters,
    AdminListActions,
    AdminPagination,
    ExpensesTableList,
  },
  data() {
    return {
      selected: [],
    };
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.expenses,
        ...locales.en.forms,
      },
      fr: {
        ...locales.fr.expenses,
        ...locales.fr.forms,
      },
    },
  },
};
</script>

<style lang="scss"></style>
