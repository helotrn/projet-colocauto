<template>
  <validation-provider
    class="forms-validated-input"
    :mode="mode"
    :name="label"
    :rules="rulesOrNothing"
    v-slot="validationContext"
  >
    <b-form-group
      :label="type !== 'checkbox' ? label : ''"
      :label-for="name"
      :label-cols="inline"
      :description="type === 'image' ? null : description"
      v-b-tooltip.hover
      :title="disabled ? disabledTooltip : ''"
    >
      <b-form-select
        v-if="type === 'select'"
        :id="name"
        :name="name"
        :state="getValidationState(validationContext)"
        :options="options"
        :disabled="disabled"
        v-bind:value="value"
        v-on:change="emitInput"
      />
      <b-form-checkbox
        v-else-if="type === 'checkbox'"
        :id="name"
        :name="name"
        :value="true"
        :disabled="disabled"
        :unchecked-value="false"
        :state="getValidationState(validationContext)"
        :checked="value"
        @change="emitInput"
      >
        <span v-html="label" />
      </b-form-checkbox>
      <b-form-checkbox-group
        v-else-if="type === 'checkboxes'"
        :switches="switches"
        :stacked="stacked"
        :id="name"
        :name="name"
        :disabled="disabled"
        :options="options"
        :state="getValidationState(validationContext)"
        :checked="value"
        @change="emitInput"
      />
      <b-form-textarea
        v-else-if="type === 'textarea'"
        :id="name"
        :name="name"
        :description="description"
        :placeholder="placeholder"
        :disabled="disabled"
        :rows="rows"
        :max-rows="maxRows"
        :state="getValidationState(validationContext)"
        v-bind:value="value"
        v-on:input="emitInput"
      />
      <forms-map-input
        v-else-if="type === 'point'"
        bounded
        :center="center"
        :disabled="disabled"
        :state="getValidationState(validationContext)"
        :polygons="polygons"
        v-bind:value="value"
        v-on:input="emitInput"
      />
      <forms-date-picker
        v-else-if="type === 'date'"
        :disabled-dates="disabledDates"
        :disabled-dates-fct="disabledDatesFct"
        :disabled="disabled"
        :initial-view="initialView"
        :state="getValidationState(validationContext)"
        :value="value"
        :open-date="openDate"
        @input="emitInput"
      />
      <forms-date-time-picker
        v-else-if="type === 'datetime'"
        :disabled-dates="disabledDates"
        :disabled-dates-fct="disabledDatesFct"
        :disabled-times-fct="disabledTimesFct"
        :disabled="disabled"
        :value="value"
        @input="emitInput"
      />
      <b-form-input
        v-else-if="type === 'password'"
        :id="name"
        :name="name"
        type="password"
        :placeholder="placeholder"
        :disabled="disabled"
        :state="getValidationState(validationContext)"
        v-bind:value="value"
        v-on:input="emitInput"
      />
      <forms-files-uploader
        v-else-if="type === 'files'"
        :id="name"
        :name="name"
        :field="name"
        :placeholder="placeholder"
        :disabled="disabled"
        :value="value"
        @input="emitInput"
      />
      <forms-file-uploader
        v-else-if="type === 'file'"
        :id="name"
        :name="name"
        :field="name"
        :placeholder="placeholder"
        :disabled="disabled"
        :value="value"
        @input="emitInput"
      />
      <forms-image-uploader
        v-else-if="type === 'image'"
        :id="name"
        :name="name"
        :field="name"
        :description="description"
        :placeholder="placeholder"
        :disabled="disabled"
        :state="getValidationState(validationContext)"
        :value="value"
        @input="emitInput"
      />
      <forms-relation-input
        v-else-if="type === 'relation'"
        :id="name"
        :name="name"
        :query="query"
        :placeholder="placeholder"
        :disabled="disabled"
        :state="getValidationState(validationContext)"
        :object-value="objectValue"
        :reset-after-select="resetAfterSelect"
        :extra-params="extraParams"
        :value="value"
        @input="emitRelationChange"
      />
      <currency-input
        v-else-if="type === 'currency'"
        :id="name"
        :name="name"
        :disabled="disabled"
        :class="`form-control ${getValidationClass(getValidationState(validationContext))}`"
        locale="fr"
        :currency="{ suffix: '$' }"
        :value-range="{ min, max }"
        :allow-negative="false"
        v-bind:value="floatValue"
        v-on:input="emitInput"
      />
      <b-form-input
        v-else-if="type === 'number'"
        :id="name"
        :name="name"
        type="number"
        :min="min"
        :max="max"
        :step="step"
        :placeholder="placeholder"
        :disabled="disabled"
        :state="getValidationState(validationContext)"
        v-bind:value="value"
        v-on:input="emitInput"
      />
      <b-form-input
        v-else-if="!!mask"
        :id="name"
        :name="name"
        type="text"
        v-mask="mask"
        masked
        :placeholder="placeholder"
        :disabled="disabled"
        :state="getValidationState(validationContext)"
        :value="value"
        @input="emitInput"
      />
      <vue-editor v-else-if="type === 'html'" :value="value" @input="emitInput" />
      <b-form-input
        v-else
        :id="name"
        :name="name"
        type="text"
        :placeholder="placeholder"
        :disabled="disabled"
        :state="getValidationState(validationContext)"
        v-bind:value="value"
        v-on:input="emitInput"
      />
      <b-form-invalid-feedback :state="getValidationState(validationContext)">
        {{ validationContext.errors[0] }}
      </b-form-invalid-feedback>
    </b-form-group>
  </validation-provider>
</template>

<script>
import { CurrencyInput } from "vue-currency-input";
import { VueEditor } from "vue2-editor";

import FormsDatePicker from "@/components/Forms/DatePicker.vue";
import FormsDateTimePicker from "@/components/Forms/DateTimePicker.vue";
import FormsFileUploader from "@/components/Forms/FileUploader.vue";
import FormsFilesUploader from "@/components/Forms/FilesUploader.vue";
import FormsImageUploader from "@/components/Forms/ImageUploader.vue";
import FormsMapInput from "@/components/Forms/MapInput.vue";
import FormsRelationInput from "@/components/Forms/RelationInput.vue";

export default {
  name: "FormsValidatedInput",
  props: {
    center: {
      type: Object,
      required: false,
      default: null,
    },
    description: {
      type: String,
      required: false,
      default: "",
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
    disabledDatesFct: {
      type: Function,
      required: false,
      default: () => false,
    },
    disabledTimesFct: {
      type: Function,
      required: false,
      default: () => false,
    },
    disabledTooltip: {
      type: String,
      required: false,
      default: "",
    },
    extraParams: {
      type: Object,
      requird: false,
      default() {
        return {};
      },
    },
    initialView: {
      type: String,
      required: false,
      default: null,
    },
    inline: {
      type: [String],
      required: false,
      default: null,
    },
    label: {
      type: String,
      required: true,
    },
    mode: {
      required: false,
      type: String,
      default: "eager",
    },
    mask: {
      required: false,
      type: String,
      default: "",
    },
    max: {
      type: Number,
      required: false,
      default: Number.MAX_SAFE_INTEGER,
    },
    maxRows: {
      type: Number,
      required: false,
      default: 6,
    },
    min: {
      type: Number,
      required: false,
      default: -Number.MAX_SAFE_INTEGER,
    },
    name: {
      type: String,
      required: true,
    },
    objectValue: {
      type: Object,
      required: false,
      default: null,
    },
    openDate: {
      type: Date,
      required: false,
      default() {
        return new Date();
      },
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
      default: "",
    },
    polygons: {
      type: Array,
      required: false,
      default() {
        return [];
      },
    },
    query: {
      type: Object,
      required: false,
      default: null,
    },
    resetAfterSelect: {
      type: Boolean,
      required: false,
      default: false,
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
    CurrencyInput,
    FormsDatePicker,
    FormsDateTimePicker,
    FormsFileUploader,
    FormsFilesUploader,
    FormsImageUploader,
    FormsMapInput,
    FormsRelationInput,
    VueEditor,
  },
  computed: {
    floatValue() {
      return parseFloat(this.value);
    },
    rulesOrNothing() {
      if (!this.rules) {
        return "";
      }

      if (this.type === "point") {
        return {
          ...this.rules,
          length: 2,
        };
      }

      return this.rules;
    },
  },
  methods: {
    emitInput(value) {
      this.$emit("input", value);
    },
    emitRelationChange(value) {
      this.$emit("relation", value);
    },
    getValidationState({ dirty, validated, valid = null }) {
      if (this.rulesOrNothing === "") {
        return null;
      }

      if (dirty && !validated) {
        return null;
      }

      return validated ? valid : null;
    },
    getValidationClass(state) {
      switch (state) {
        case true:
          return "is-valid";
        case false:
          return "is-invalid";
        default:
          return "";
      }
    },
  },
};
</script>

<style lang="scss"></style>
