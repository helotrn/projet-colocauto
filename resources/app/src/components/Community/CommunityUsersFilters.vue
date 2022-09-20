<template>
  <div class="community-users-filters">
    <b-button variant="info" v-b-modal="'community-users-filters__filter-pane'">
      {{ $t("components.admin.filters.filters") | capitalize }}
    </b-button>

    <b-modal
      id="community-users-filters__filter-pane"
      :title="$t('components.admin.filters.filters') | capitalize"
    >
      <!-- TODO (#1078) -->
      <b-form-group
        v-if="visibleFields.includes('id')"
        :key="'id'"
        :label="fieldDefs.find((f) => f.key == 'id').label"
        :label-for="fieldDefs.find((f) => f.key == 'id').key"
      >
        <b-form-input
          type="text"
          :value="item.id"
          :name="fieldDefs.find((f) => f.key == 'id').key"
          :id="'id'"
          @change="onInputChange('id', $event)"
        />
      </b-form-group>

      <!-- TODO (#1078) -->
      <b-form-group
        v-if="visibleFields.includes('user_id')"
        :key="'user_id'"
        :label="fieldDefs.find((f) => f.key == 'user_id').label"
        :label-for="fieldDefs.find((f) => f.key == 'user_id').key"
      >
        <b-form-input
          type="text"
          :value="item.user_id"
          :name="fieldDefs.find((f) => f.key == 'user_id').key"
          :id="'user_id'"
          @change="onInputChange('user_id', $event)"
        />
      </b-form-group>

      <!-- TODO (#1078) -->
      <b-form-group
        v-if="visibleFields.includes('user_full_name')"
        :key="'user_full_name'"
        :label="fieldDefs.find((f) => f.key == 'user_full_name').label"
        :label-for="fieldDefs.find((f) => f.key == 'user_full_name').key"
      >
        <b-form-input
          type="text"
          :value="item.user_full_name"
          :name="fieldDefs.find((f) => f.key == 'user_full_name').key"
          :id="'user_full_name'"
          @change="onInputChange('user_full_name', $event)"
        />
      </b-form-group>
    </b-modal>
  </div>
</template>

<script>
export default {
  name: "CommunityUsersFilters",
  props: {
    visibleFields: {
      type: Array,
      required: false,
      default: () => {
        return ["id", "user_id", "user_full_name"];
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
          },
        ];
      },
    },
    item: {
      type: [Object, Function],
      required: false,
    },
  },
  methods: {
    onInputChange(name, value) {
      let newValue = this.item;
      newValue[name] = value;

      this.$emit("change", newValue);
    },
  },
};
</script>

<style lang="scss">
.community-users-filters {
}
</style>
