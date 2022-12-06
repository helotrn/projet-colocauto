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
      empty-text="Pas d'invitation"
    >
      <template v-slot:cell(actions)="row">
        <admin-list-actions :columns="['edit']" :row="row" slug="invitations" />
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
  </div>
</template>

<script>
import AdminListActions from "@/components/Admin/ListActions.vue";

export default {
  name: "InvitationsTableList",
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
          "email",
          "token",
          "community.name",
          "status",
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
          { key: "email", label: this.$t("invitations.fields.email"), sortable: true },
          { key: "token", label: this.$t("invitations.fields.token"), sortable: true },
          {
            key: "community.name",
            label: this.$t("communities.fields.community.name"),
            sortable: true,
          },
          { key: "status", label: this.$t("invitations.list.status") },
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
