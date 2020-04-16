<template>
  <div class="admin-filters">
    <b-button variant="info" v-b-toggle.collapse-filters>
      Filtres <small>({{ $tc(
        'components.admin.filters.{count} filtre appliqué',
        appliedFilters.length,
        { count: appliedFilters.length }
      ) }})</small>
    </b-button>

    <b-collapse id="collapse-filters" class="admin-filters__collapse">
      <b-card no-body bg-variant="eggshell" border-variant="dark" class="shadow">
        <b-card-body>
          <b-form-group v-for="(def, key) in filters" :key="key"
            :label="$t(`${entity}.fields.${key}`) | capitalize" :label-for="key">
            <b-form-select v-if="Array.isArray(def)" v-model="params[key]"
              :id="key" :name="key">
              <b-form-select-option :value="null">
                {{ $t(`${entity}.${key}s.null`) | capitalize }}
              </b-form-select-option>
              <b-form-select-option v-for="value in def" :key="value" :value="value">
                {{ $t(`${entity}.${key}s.${value}`) | capitalize }}
              </b-form-select-option>
            </b-form-select>
            <b-form-input v-else-if="def === 'date'" type="date"
              v-model="params[key]" :name="key" :id="key" />
            <b-form-input v-else type="text"
              v-model="params[key]" :name="key" :id="key" />
          </b-form-group>
        </b-card-body>
      </b-card>
    </b-collapse>
  </div>
</template>

<script>
export default {
  name: 'AdminFilters',
  props: {
    entity: {
      type: String,
      required: true,
    },
    filters: {
      type: Object,
      required: true,
    },
    params: {
      type: Object,
      required: true,
    },
  },
  computed: {
    appliedFilters() {
      return Object.keys(this.filters).filter(f => !!this.params[f]);
    },
  },
};
</script>

<style lang="scss">
.admin-filters {
  margin-bottom: 10px;

  .collapse {
    padding-top: 10px;
    position: absolute;
    right: $grid-gutter-width / 2;
    z-index: 100;
    min-width: 50%;

    .card {
      width: 100%;
      text-align: left;
    }
  }
}
</style>
