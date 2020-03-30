<template>
  <div class="forms-builder">
    <forms-validated-input v-for="(def, key) in definition" :key="key"
      :label="$t(`${entity}.fields.${key}`) | capitalize"
      :name="key" :rules="def.rules" :type="def.type"
      :options="def.options" :disabled="disabled || def.disabled"
      :placeholder="placeholderOrLabel(key) | capitalize"
      :query="def.query" :object-value="objectValue(key)" @relation="updateObject($event, key)"
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
      this.item[objectKey] = selection;

      if (!selection) {
        this.item[key] = null;
      } else {
        this.item[key] = selection.id;
      }
    },
  },
};
</script>

<style lang="scss">
</style>
