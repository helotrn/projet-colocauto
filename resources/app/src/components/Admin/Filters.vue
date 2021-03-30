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
            :label="getLabelFor(def, key)"
            :label-for="key">
            <b-form-select v-if="Array.isArray(def)"
              :value="params[key]" @input="setParam(key, $event)"
              :id="key" :name="key">
              <b-form-select-option :value="null">
                {{ $t(`${entity}.fields.${key}s.null`) | capitalize }}
              </b-form-select-option>
              <b-form-select-option v-for="value in def" :key="value" :value="value">
                {{ $t(`${entity}.fields.${key}s.${value}`) | capitalize }}
              </b-form-select-option>
            </b-form-select>
            <div v-else-if="def === 'boolean'">
              <b-form-checkbox
                :id="key" :name="key"
                :value="true"
                :unchecked-value="false"
                :checked="params[key]"
                @change="setParam(key, $event)">
                <span v-html="$filters.capitalize($t(`${entity}.fields.${key}`))" />
              </b-form-checkbox>
            </div>
            <div v-else-if="def === 'date'">
              <forms-date-range-picker
                :value="params[key]"
                @input="setParam(key, $event)" />
            </div>
            <forms-relation-input v-else-if="def.type === 'relation'"
              :id="key" :name="key" :query="def.query"
              :value="params[key]"
              @input="emitRelationChange(key, $event)" />
            <b-form-input v-else-if="def.type === 'relation'" type="text"
              :value="params[key]" @input="setParam(key, $event)"
              :name="key" :id="key" />
            <b-form-input v-else type="text"
              :value="params[key]" @input="setParam(key, $event)"
              :name="key" :id="key" />
          </b-form-group>
        </b-card-body>
      </b-card>
    </b-collapse>
  </div>
</template>

<script>
import FormsDateRangePicker from '@/components/Forms/DateRangePicker.vue';
import FormsRelationInput from '@/components/Forms/RelationInput.vue';

export default {
  name: 'AdminFilters',
  components: {
    FormsDateRangePicker,
    FormsRelationInput,
  },
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
  methods: {
    emitRelationChange(name, event) {
      this.setParam(name, event ? event.value : null);
    },
    getLabelFor(type, key) {
      return type !== 'boolean'
        ? this.$filters.capitalize(this.$t(`${this.entity}.fields.${key}`))
        : '';
    },
    setParam(name, value) {
      if (!value) {
        this.$emit('change', { name, value: undefined });
      } else {
        this.$emit('change', { name, value });
      }
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
