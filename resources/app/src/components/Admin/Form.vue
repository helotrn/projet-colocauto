<template>
  <div class="admin-form">
    <b-form-group v-for="(def, key) in definition" :key="key"
      :description="def.description"
      :label="$t(`${entity}.fields.${key}`) | capitalize"
      :label-for="key">
      <b-form-select v-if="def.type === 'select'" v-model="item[key]"
        :id="key" :name="key"
        :options="def.options" />
      <b-form-input v-else-if="def.type === 'number'" :type="def.type"
        v-model="item[key]" :name="key" :id="key"
        :placeholder="placeholderOrLabel(key) | capitalize"
        :disabled="def.disabled" />
      <b-form-input v-else-if="def.type === 'text'" :type="def.type"
        v-model="item[key]" :name="key" :id="key"
        :placeholder="placeholderOrLabel(key) | capitalize" :disabled="def.disabled" />
      <b-form-textarea v-else-if="def.type === 'textarea'"
        :id="key" :name="key"
        v-model="item[key]"
        :placeholder="placeholderOrLabel(key) | capitalize"
        :rows="def.rows || 3" :max-rows="def.maxRows || 6" />
      <div v-else>Non supporté : {{ def.type }}</div>
    </b-form-group>
  </div>
</template>

<script>
export default {
  name: 'Form',
  props: {
    definition: {
      type: Object,
      required: true,
    },
    entity: {
      type: String,
      required: true,
    },
    item: {
      type: Object,
      required: true,
    },
  },
  methods: {
    placeholderOrLabel(key) {
      if (this.$i18n.te(`${this.entity}.placeholders.${key}`)) {
        return this.$i18n.t(`${this.entity}.placeholders.${key}`);
      }

      return this.label(key);
    },
    label(key) {
      return this.$i18n.t(`${this.entity}.fields.${key}`);
    },
  },
};
</script>

<style lang="scss">
</style>
