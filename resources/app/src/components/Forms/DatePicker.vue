<template>
  <datepicker
    :class="{
      'forms-datepicker': true,
      'is-valid': state === true,
      'is-invalid': state === false,
    }"
    :inline="inline"
    :clear-button="clearButton"
    input-class="form-control"
    :format="format"
    :initial-view="initialView"
    :open-date="openDate"
    :language="language"
    :disabled-dates="disabledDates"
    :disabled="disabled"
    :value="dateValue"
    @selected="emitInput($event)"
  />
</template>

<script>
import Datepicker from "vuejs-datepicker";
import { fr } from "vuejs-datepicker/dist/locale";

export default {
  name: "FormsDatePicker",
  components: { Datepicker },
  props: {
    clearButton: {
      type: Boolean,
      required: false,
      default: false,
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
    format: {
      type: [String, Function],
      required: false,
      default() {
        return (val) => {
          if (!val) {
            return "";
          }

          return this.$dayjs(val).format("D MMMM YYYY").toLowerCase();
        };
      },
    },
    initialView: {
      type: String,
      required: false,
      default: "day",
    },
    inline: {
      type: Boolean,
      required: false,
      default: false,
    },
    openDate: {
      type: Date,
      required: false,
      default() {
        return new Date();
      },
    },
    selected: {
      type: Function,
      required: false,
      default() {
        return () => {};
      },
    },
    state: {
      type: Boolean,
      required: false,
      default: null,
    },
    value: {
      type: String,
      required: false,
    },
  },
  computed: {
    dateValue() {
      if (!this.value || this.value === "null") {
        return null;
      }

      return this.$dayjs(this.value, { timeZone: "America/Montreal" }).toDate();
    },
    language() {
      return fr;
    },
  },
  methods: {
    emitInput(value) {
      this.$emit("input", value ? value.format("YYYY-MM-DD") : value);
    },
  },
};
</script>

<style lang="scss">
.forms-datepicker {
  input.form-control[readonly]:not([disabled]) {
    background-color: $white;
  }

  .vdp-datepicker__clear-button {
    position: absolute;
    right: 0;
    bottom: 0;
    padding: 8px;
    transition: transform $one-tick ease-in-out;

    &:hover {
      transform: scale(1.4);
    }
  }
}
</style>
