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
      empty-text="Pas de remboursements"
    >
      <template v-slot:cell(executed_at)="row">
        {{ row.value | date }}
      </template>
      <template v-slot:cell(changes)="row">
        <div>{{ row.item.changes.length }}</div>
      </template>
      <template v-slot:cell(actions)="row">
        <admin-list-actions :columns="['edit']" :row="row" slug="refunds" />
      </template>
    </b-table>
  </div>
</template>

<script>
import AdminListActions from "@/components/Admin/ListActions.vue";

export default {
  name: "RefundsTableList",
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
          { key: "amount", label: this.$t("refunds.fields.amount"), sortable: true },
          {
            key: "user.full_name",
            label: this.$t("refunds.fields.user_id"),
            sortable: true,
          },
          {
            key: "credited_user.full_name",
            label: this.$t("refunds.fields.credited_user_id"),
            sortable: true,
          },
          {
            key: "community.name",
            label: this.$t("communities.fields.community.name"),
            sortable: true,
          },
          { key: "executed_at", label: this.$t("refunds.fields.executed_at"), sortable: true },
          { key: "changes", label: 'Modifs' },
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
