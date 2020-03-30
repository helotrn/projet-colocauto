<template>
  <datepicker
    :class="{
      'forms-datepicker': true,
      'is-valid': state === true,
      'is-invalid': state === false,
    }" :inline="inline"
    input-class="form-control"
    :format="format"
    :initial-view="initialView"
    :open-date="openDate"
    :language="language"
    :disabled-dates="disabledDates"
    :disabled="disabled"
    :value="dateValue"
    @selected="emitInput($event)" />
</template>

<script>
import Datepicker from 'vuejs-datepicker';
import { fr } from 'vuejs-datepicker/dist/locale';

export default {
  name: 'FormsDatePicker',
  components: { Datepicker },
  props: {
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
            return '';
          }

          return this.$dayjs(val).format('D MMMM YYYY').toLowerCase();
        };
      },
    },
    initialView: {
      type: String,
      required: false,
      default: 'day',
    },
    inline: {
      type: Boolean,
      required: false,
      default: false,
    },
    openDate: {
      type: String,
      required: false,
      default: '2001-01-01',
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
      if (!this.value) {
        return null;
      }

      return this.$dayjs(this.value, { timeZone: 'America/Montreal' }).toDate();
    },
    language() {
      return fr;
    },
  },
  methods: {
    emitInput(value) {
      this.$emit('input', value.format('YYYY-MM-DD'));
    },
  },
};
</script>

<style lang="scss">
.forms-datepicker {
  input.form-control[readonly]:not([disabled]) {
    background-color: $white;
  }
}
</style>
