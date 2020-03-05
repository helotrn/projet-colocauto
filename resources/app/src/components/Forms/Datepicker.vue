<template>
  <datepicker class="forms-datepicker" :inline="inline"
    input-class="form-control"
    :format="format"
    :language="language"
    :disabled-dates="disabledDates"
    :value="dateValue"
    @selected="emitInput($event)" />
</template>

<script>
import Datepicker from 'vuejs-datepicker';
import { fr, en } from 'vuejs-datepicker/dist/locale'

export default {
  name: 'FormsDatepicker',
  components: { Datepicker },
  props: {
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
        }
      },
    },
    inline: {
      type: Boolean,
      required: false,
      default: false,
    },
    selected: {
      type: Function,
      required: false,
      default() {
        return () => {};
      },
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
      this.$emit('input', value.format('YYYY-MM-DD'))
    },
  },
};
</script>

<style lang="scss">
</style>
