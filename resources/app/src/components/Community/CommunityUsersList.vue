<template>
  <div>
    <b-table striped hover :fields="fields" :items="items">
      <template v-slot:cell(user_full_name)="row">
        <!-- Allow an URL-generating function in field definition -->
        <router-link v-if="row.field.urlFct" :to="row.field.urlFct(row.item)">
          {{ row.item.user_full_name }}
        </router-link>
        <template v-else>
          {{ row.item.user_full_name }}
        </template>
      </template>

      <template v-slot:cell(community_name)="row">
        <!-- Allow an URL-generating function in field definition -->
        <router-link v-if="row.field.urlFct" :to="row.field.urlFct(row.item)">
          {{ row.item.community_name }}
        </router-link>
        <template v-else>
          {{ row.item.community_name }}
        </template>
      </template>

      <template v-slot:cell(role)="row">
        <b-select
          :options="[
            { value: 'member', text: $t('communities.fields.user.role_labels.member') },
            { value: 'admin', text: $t('communities.fields.user.role_labels.admin') },
          ]"
          :value="row.item.role"
          @change="$emit('changeUserRole', row.item, $event)"
        />
      </template>

      <template v-slot:cell(approved_at)="row">
        <span>{{ row.item.approved_at | date }}</span>
      </template>

      <template v-slot:cell(suspended_at)="row">
        <span>{{ row.item.suspended_at | date }}</span>
      </template>
    </b-table>
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
          {
            key: "user_full_name",
            label: this.$t("communities.fields.user.name"),
            urlFct: function (item) {
              return `/admin/users/${item.user_id}`;
            },
          },
          { key: "community_id", label: this.$t("communities.fields.community.id") },
          {
            key: "community_name",
            label: this.$t("communities.fields.community.name"),
            urlFct: function (item) {
              return `/admin/communities/${item.community_id}`;
            },
          },
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
