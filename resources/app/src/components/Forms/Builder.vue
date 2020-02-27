<template>
  <div class="forms-builder">
    <forms-validated-input v-for="(def, key) in definition" :key="key"
      :label="def.type !== 'checkbox' ? $t(`${entity}.fields.${key}`) : '' | capitalize"
      :name="key" :rules="def.rules" :type="def.type" :options="def.options"
      :placeholder="placeholderOrLabel(key) | capitalize"
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
