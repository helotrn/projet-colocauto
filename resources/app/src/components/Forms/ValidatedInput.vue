<template>
  <validation-provider class="forms-validated-input"
    :mode="mode"
    :name="name"
    :rules="rulesOrNothing"
    v-slot="validationContext">
    <b-form-group :label="type !== 'checkbox' ? label : ''" :label-for="name"
      :description="description" v-b-tooltip.hover :title="disabled ? disabledTooltip : ''">
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
      <b-form-checkbox-group v-else-if="type === 'checkboxes'"
        :switches="switches" :stacked="stacked"
        :id="name" :name="name" :key="`${type}-${name}`"
        :disabled="disabled" :options="options"
        :state="getValidationState(validationContext)"
        :checked="value"
        @change="emitChange" />
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
        :state="getValidationState(validationContext)"
        v-bind:value="value"
        v-on:input="emitChange" />
      <forms-date-picker v-else-if="type === 'date'"
        :disabled-dates="disabledDates"
        :disabled="disabled"
        :state="getValidationState(validationContext)"
        :value="value"
        @input="emitChange" />
      <forms-date-time-picker v-else-if="type === 'datetime'"
        :disabled-dates="disabledDates"
        :disabled-times="disabledTimes"
        :disabled="disabled"
        :value="value"
        @input="emitChange" />
      <b-form-input v-else-if="type === 'password'"
        :id="name" :name="name" :key="`${type}-${name}`"
        type="password"
        :placeholder="placeholder" :disabled="disabled"
        :state="getValidationState(validationContext)"
        v-bind:value="value"
        v-on:input="emitChange"/>
      <div v-else-if="type === 'relation'">
        <strong>Relation {{name}}</strong>
      </div>
      <b-form-input v-else-if="type === 'number'"
        :id="name" :name="name" :key="`${type}-${name}`"
        type="number" :min="min" :max="max"
        :step="step"
        :placeholder="placeholder" :disabled="disabled"
        :state="getValidationState(validationContext)"
        v-bind:value="value"
        v-on:input="emitChange"/>
      <b-form-input v-else-if="!!mask"
        :id="name" :name="name" :key="`${type}-${name}`"
        type="text" v-mask="mask" masked
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
import FormsDatePicker from '@/components/Forms/DatePicker.vue';
import FormsDateTimePicker from '@/components/Forms/DateTimePicker.vue';
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
    disabledDates: {
      type: Object,
      required: false,
      default() {
        return {};
      },
    },
    disabledTimes: {
      type: Object,
      required: false,
      default() {
        return {};
      },
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
    mode: {
      required: false,
      type: String,
      default: 'eager',
    },
    mask: {
      required: false,
      type: String,
      default: '',
    },
    max: {
      type: Number,
      required: false,
      default: null,
    },
    maxRows: {
      type: Number,
      required: false,
      default: 6,
    },
    min: {
      type: Number,
      required: false,
      default: null,
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
    stacked: {
      type: Boolean,
      required: false,
      default: true,
    },
    step: {
      type: Number,
      required: false,
      default: 1,
    },
    switches: {
      type: Boolean,
      required: false,
      default: true,
    },
    type: {
      type: String,
      required: true,
    },
    value: {
      required: true,
    },
  },
  components: {
    FormsDatePicker,
    FormsDateTimePicker,
    FormsMapInput,
  },
  computed: {
    rulesOrNothing() {
      if (!this.rules) {
        return '';
      }

      if (this.type === 'point') {
        return {
          ...this.rules,
          length: 2,
        };
      }

      return this.rules;
    },
  },
  methods: {
    emitChange(value) {
      this.$emit('input', value);
    },
    getValidationState({ dirty, validated, valid = null }) {
      if (this.rulesOrNothing === '') {
        return null;
      }

      if (dirty && !validated) {
        return null;
      }

      return validated ? valid : null;
    },
  },
};
</script>

<style lang="scss">
</style>
