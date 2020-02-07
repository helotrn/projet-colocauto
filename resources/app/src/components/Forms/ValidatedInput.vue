<template>
  <validation-provider class="forms-validated-input"
    :name="name"
    :rules="rules"
    v-slot="validationContext">
    <b-form-group :label="label" :label-for="name"
      :description="description">
      <b-form-checkbox v-if="type === 'checkbox'"
        :id="name" :name="name"
        :value="true"
        :unchecked-value="false"
        :state="getValidationState(validationContext)"
        v-bind:checked="value"
        v-on:change="emitChange" />
      <b-form-textarea v-else-if="type === 'textarea'"
        :id="name" :name="name"
        :placeholder="placeholder"
        :rows="rows" :max-rows="maxRows"
        :state="getValidationState(validationContext)"
        v-bind:value="value"
        v-on:input="emitChange" />
      <b-form-input v-else
        :id="name" :name="name" :type="type"
        :placeholder="placeholder"
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
export default {
  name: 'FormsValidatedInput',
  props: {
    description: {
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
        return {};
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
