<template>
  <div>
    <b-table striped hover :fields="fields" :items="items"> </b-table>
    <b-pagination
      v-model="pageNum"
      :total-rows="totalItemCount"
      :per-page="itemsPerPage"
      @change="$emit('changePage', $event)"
    >
    </b-pagination>
  </div>
</template>

<script>
export default {
  name: "CommunityUsersList",
  props: {
    /* List of fields to display and their order. Show all by default. */
    visibleFields: {
      type: Array,
      required: false,
      default: function () {
        return [
          "id",
          "user_id",
          "user_full_name",
          "community_id",
          "community_name",
          "role",
          "approved_at",
          "suspended_at",
          "proof",
          "actions",
        ];
      },
    },
    fieldDefs: {
      type: Array,
      required: false,
      default: function () {
        return [
          { key: "id", label: this.$t("lists.id") },
          { key: "user_id", label: this.$t("communities.fields.user.id") },
          { key: "user_full_name", label: this.$t("communities.fields.user.name") },
          { key: "community_id", label: this.$t("communities.fields.community.id") },
          { key: "community_name", label: this.$t("communities.fields.community.name") },
          { key: "role", label: this.$t("communities.fields.user.role") },
          { key: "approved_at", label: this.$t("communities.fields.user.approved_at") },
          { key: "suspended_at", label: this.$t("communities.fields.user.suspended_at") },
          { key: "proof", label: this.$t("communities.fields.user.proof") },
          { key: "actions", label: this.$t("communities.fields.user.actions") },
        ];
      },
    },
    items: {
      type: [Array, Function],
      required: false,
    },
    itemsPerPage: {
      type: Number,
      required: false,
    },
    /*
      Total number of items in the unpaginated list.
    */
    totalItemCount: {
      type: Number,
      required: false,
    },
  },
  computed: {
    fields() {
      let visibleFields = [];
      for (const fieldName of this.visibleFields) {
        const fieldDef = this.fieldDefs.find((f) => f.key === fieldName);
        if (fieldDef) visibleFields.push(fieldDef);
      }

      return visibleFields;
    },
  },
  data() {
    return {
      pageNum: 1,
    };
  },
};
</script>
