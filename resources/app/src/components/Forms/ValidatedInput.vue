<template>
  <validation-provider class="forms-validated-input"
    :name="name"
    :rules="rulesOrNothing"
    v-slot="validationContext">
    <b-form-group :label="type !== 'checkbox' ? label : ''" :label-for="name"
      :description="description" v-b-tooltip.hover :title="disabledTooltip">
      <b-form-select v-if="type === 'select'"
        :id="name" :name="name" :key="`${type}-${name}`"
        :state="getValidationState(validationContext)"
        :options="options" :disabled="disabled"
        v-bind:value="value"
        v-on:change="emitChange" />
      <b-form-checkbox v-else-if="type === 'checkbox'"
        :id="name" :name="name" :key="`${type}-${name}`"
        :value="true" :disabled="disabled"
        :unchecked-value="false"
        :state="getValidationState(validationContext)"
        v-bind:checked="value"
        v-on:change="emitChange">
        {{ label }}
      </b-form-checkbox>
      <b-form-textarea v-else-if="type === 'textarea'"
        :id="name" :name="name" :key="`${type}-${name}`"
        :description="description"
        :placeholder="placeholder" :disabled="disabled"
        :rows="rows" :max-rows="maxRows"
        :state="getValidationState(validationContext)"
        v-bind:value="value"
        v-on:input="emitChange" />
      <forms-map-input v-else-if="type === 'point'"
        :center="center"
        v-bind:value="value"
        v-on:input="emitChange" />
      <forms-datepicker v-else-if="type === 'date'"
        input-class="form-control"
        v-bind:value="value"
        v-on:input="emitChange" />
      <b-form-input v-else-if="type === 'password'"
        :id="name" :name="name" :key="`${type}-${name}`"
        type="password"
        :placeholder="placeholder" :disabled="disabled"
        :state="getValidationState(validationContext)"
        v-bind:value="value"
        v-on:input="emitChange"/>
      <b-form-input v-else
        :id="name" :name="name" :key="`${type}-${name}`"
        type="text"
        :placeholder="placeholder" :disabled="disabled"
        :state="getValidationState(validationContext)"
        v-bind:value="value"
        v-on:input="emitChange"/>
      <b-form-invalid-feedback>
        {{ validationContext.errors[0] }}
      </b-form-invalid-feedback>
    </b-form-group>
  </validation-provider>
</template>

<script>
import FormsDatepicker from '@/components/Forms/Datepicker.vue';
import FormsMapInput from '@/components/Forms/MapInput.vue';

export default {
  name: 'FormsValidatedInput',
  props: {
    center: {
      type: Object,
      required: false,
      default: null,
    },
    description: {
      type: String,
      required: false,
      default: '',
    },
    disabled: {
      type: Boolean,
      required: false,
      default: false,
    },
    disabledTooltip: {
      type: String,
      required: false,
      default: '',
    },
    label: {
      type: String,
      required: true,
    },
    maxRows: {
      type: Number,
      required: false,
      default: 6,
    },
    name: {
      type: String,
      required: true,
    },
    options: {
      type: Array,
      required: false,
      default() {
        return [];
      },
    },
    placeholder: {
      type: String,
      required: false,
      default: '',
    },
    rows: {
      type: Number,
      required: false,
      default: 3,
    },
    rules: {
      type: Object,
      required: false,
      default() {
        return null;
      },
    },
    type: {
      type: String,
      required: true,
    },
    value: {
      required: true,
    },
  },
  components: { FormsDatepicker, FormsMapInput },
  computed: {
    rulesOrNothing() {
      return this.rules || '';
    },
  },
  methods: {
    emitChange(value) {
      this.$emit('input', value);
    },
    getValidationState({ dirty, validated, valid = null }) {
      return dirty || validated ? valid : null;
    },
  },
};
</script>

<style lang="scss">
</style>
