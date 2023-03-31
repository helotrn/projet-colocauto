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
          entity="refunds"
          :filters="filters"
          :params="contextParams"
          @change="setParam"
        />
      </b-col>
    </b-row>

    <b-row>
      <b-col>
        <refunds-table-list
          :visibleFields="[
            'id',
            'amount',
            'user.full_name',
            'credited_user.full_name',
            'executed_at',
            'changes',
            'actions'
          ]"
          :items="data"
          :busy="loading"
        >
        </refunds-table-list>
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
import RefundsTableList from "@/components/Refunds/TableList.vue";

import DataRouteGuards from "@/mixins/DataRouteGuards";
import ListMixin from "@/mixins/ListMixin";
import locales from "@/locales";

export default {
  name: "AdminRefunds",
  mixins: [DataRouteGuards, ListMixin],
  components: {
    AdminFilters,
    AdminListActions,
    AdminPagination,
    RefundsTableList,
  },
  data() {
    return {
      selected: [],
    };
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.refunds,
        ...locales.en.forms,
      },
      fr: {
        ...locales.fr.refunds,
        ...locales.fr.forms,
      },
    },
  },
};
</script>

<style lang="scss"></style>
