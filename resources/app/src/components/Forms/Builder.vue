<template>
  <div class="forms-builder">
    <forms-validated-input v-for="(def, key) in definition" :key="key"
      :label="$t(`${entity}.fields.${key}`) | capitalize"
      :name="key" :rules="def.rules" :type="def.type"
      :options="def.options" :disabled="disabled || def.disabled"
      :placeholder="placeholderOrLabel(key) | capitalize"
      :initial-view="def.initial_view"
      :query="def.query" :object-value="objectValue(key)"
      @relation="updateObject($event, key)"
      v-model="item[key]" />
  </div>
</template>

<script>
import FormsValidatedInput from '@/components/Forms/ValidatedInput.vue';

export default {
  name: 'FormsBuilder',
  components: { FormsValidatedInput },
  props: {
    definition: {
      type: Object,
      required: true,
    },
    disabled: {
      type: Boolean,
      required: false,
      default: false,
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
    label(key) {
      return this.$i18n.t(`${this.entity}.fields.${key}`);
    },
    objectValue(key) {
      if (key.indexOf('_id') !== -1) {
        const objectName = key.replace('_id', '');
        return this.item[objectName];
      }

      if (this.item[key]
        && typeof this.item[key] === 'object'
        && !Array.isArray(this.item[key])) {
        return this.item[key];
      }

      return null;
    },
    placeholderOrLabel(key) {
      if (this.$i18n.te(`${this.entity}.placeholders.${key}`)) {
        return this.$i18n.t(`${this.entity}.placeholders.${key}`);
      }

      return this.label(key);
    },
    updateObject(selection, key) {
      const objectKey = key.replace('_id', '');
      const newItem = { ...this.item };

      newItem[objectKey] = selection;

      if (!selection) {
        newItem[key] = null;
      } else {
        newItem[key] = selection.id;
      }

      this.$emit('input', newItem);
    },
  },
};
</script>

<style lang="scss">
</style>
