<template>
  <div>
    <b-table
      striped
      hover
      :items="items"
      :busy="busy"
      :fields="fields"
      no-sort-reset
      :show-empty="true"
      empty-text="Pas de dépenses"
    >
      <template v-slot:cell(executed_at)="row">
        {{ new Date(row.value).toLocaleDateString('fr', {day:'numeric', month: 'short', year: 'numeric'}) }}
      </template>
      <template v-slot:cell(tag)="row">
        <span class="badge" :class="`badge-${row.value.color}`">{{row.value.name}}</span>
      </template>
      <template v-slot:cell(actions)="row">
        <admin-list-actions :columns="['edit']" :row="row" slug="expenses" />
      </template>
    </b-table>
  </div>
</template>

<script>
import AdminListActions from "@/components/Admin/ListActions.vue";

export default {
  name: "ExpensesTableList",
  components: {
    AdminListActions,
  },
  props: {
    busy: {
      type: Boolean,
      required: false,
    },
    /* List of fields to display and their order. Show all by default. */
    visibleFields: {
      type: Array,
      required: false,
      default: () => {
        return [
          "id",
          "name",
          "actions",
        ];
      },
    },
    fieldDefs: {
      type: Array,
      required: false,
      default: function () {
        return [
          { key: "id", label: this.$t("lists.id"), sortable: true },
          { key: "name", label: this.$t("expenses.fields.name"), sortable: true },
          { key: "amount", label: this.$t("expenses.fields.amount"), sortable: true },
          {
            key: "user.name",
            label: this.$t("expenses.fields.user_id"),
            sortable: true,
          },
          {
            key: "loanable.name",
            label: this.$t("expenses.fields.loanable_id"),
            sortable: true,
          },
          { key: "executed_at", label: this.$t("expenses.fields.executed_at"), sortable: true },
          {
            key: "tag",
            label: this.$t("expenses.fields.expense_tag_id"),
            sortable: true,
          },
          { key: "actions", label: this.$t("communities.fields.user.actions") },
        ];
      },
    },
    items: {
      type: [Array],
      required: false,
    },
  },
  computed: {
    fields() {
      let visibleFieldDefs = [];
      for (const fieldName of this.visibleFields) {
        const fieldDef = this.fieldDefs.find((f) => f.key === fieldName);
        if (fieldDef) visibleFieldDefs.push(fieldDef);
      }

      return visibleFieldDefs;
    },
  },
};
</script>
