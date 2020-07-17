<template>
  <div class="admin-pagination">
    <b-pagination align="right" :value="params.page"
      :total-rows="total" :per-page="params.per_page"
      @input="emitChange('page', $event)" />

    <b-form inline class="admin-pagination__per_page">
      <b-form-select id="per_page" name="per_page"
        :options="[10,20,50,100]" :value="params.per_page"
        @input="emitChange('per_page', $event)" />
      <label for="per_page" class="ml-2">par page</label>
    </b-form>
    <p v-if="total">sur un total de {{ total }}</p>
  </div>
</template>

<script>
export default {
  name: 'AdminPagination',
  props: {
    params: {
      type: Object,
      required: true,
    },
    total: {
      type: Number,
      required: false,
      default: null,
    },
  },
  methods: {
    emitChange(name, value) {
      this.$emit('change', { name, value });
    },
  },
};
</script>

<style lang="scss">
.admin-pagination {
  text-align: right;

  &__per_page {
    justify-content: flex-end;
  }
}
</style>
