<template>
  <div class="admin-form">
    <b-form-group v-for="(def, key) in definition" :key="key"
      :description="def.description"
      :label="def.label"
      :label-for="key">
      <b-form-select v-if="def.type === 'select'" v-model="item[key]"
        :id="key" :name="key"
        :options="def.options" />
      <b-form-input v-else-if="def.type === 'number'" :type="def.type"
        v-model="item[key]" :name="key" :id="key"
        :placeholder="def.placeholder" :disabled="def.disabled" />
      <b-form-input v-else-if="def.type === 'text'" :type="def.type"
        v-model="item[key]" :name="key" :id="key"
        :placeholder="def.placeholder" :disabled="def.disabled" />
      <b-form-textarea v-else-if="def.type === 'textarea'"
        :id="key" :name="key"
        v-model="item[key]"
        :placeholder="def.placeholder"
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
    item: {
      type: Object,
      required: true,
    },
  },
};
</script>

<style lang="scss">
</style>
