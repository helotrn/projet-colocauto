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
    fields: {
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
  data() {
    return {
      pageNum: 1,
    };
  },
};
</script>
