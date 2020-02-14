<template>
  <div class="forms-builder">
    <b-form-group v-for="(def, key) in definition" :key="key"
      :description="def.description"
      :label="def.type !== 'checkbox' ? $t(`${entity}.fields.${key}`) : '' | capitalize"
      :label-for="key">
      <b-form-select v-if="def.type === 'select'"
        :id="key" :name="key"
        v-bind:value="item[key]"
        v-on:input="commitChange(key, $event)"
        :options="def.options" />
      <b-form-checkbox v-else-if="type === 'checkbox'"
        :name="key" :id="key"
        v-bind:checked="item[key]"
        v-on:change="commitChange(key, $event)">
        {{ $t(`${entity}.fields.${key}`) | capitalize }}
      </b-form-checkbox>
      <b-form-input v-else-if="def.type === 'number'" :type="def.type"
        :name="key" :id="key"
        v-bind:value="item[key]"
        v-on:input="commitChange(key, $event)"
        :placeholder="placeholderOrLabel(key) | capitalize"
        :disabled="def.disabled" />
      <b-form-input v-else-if="def.type === 'text'" :type="def.type"
        :name="key" :id="key"
        v-bind:value="item[key]"
        v-on:input="commitChange(key, $event)"
        :placeholder="placeholderOrLabel(key) | capitalize"
        :disabled="def.disabled" />
      <b-form-textarea v-else-if="def.type === 'textarea'"
        :id="key" :name="key"
        v-bind:value="item[key]"
        v-on:input="commitChange(key, $event)"
        :placeholder="placeholderOrLabel(key) | capitalize"
        :rows="def.rows || 3" :max-rows="def.maxRows || 6" />
      <div v-else>Non supporté : {{ def.type }}</div>
    </b-form-group>
  </div>
</template>

<script>
export default {
  name: 'FormsBuilder',
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
    commitChange(key, value) {
      this.$store.commit(`${this.entity}/item`, {
        ...this.item,
        [key]: value,
      });
    },
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
