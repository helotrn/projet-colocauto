<template>
  <b-row no-gutter class="forms-date-time-picker">
    <b-col col lg="12">
      <forms-date-picker
        :disabled="disabled"
        :disabled-dates="disabledDates"
        :value="dateValue"
        @input="emitChangeDate" />
    </b-col>

    <b-col col lg="12">
      <timeselector :h24="true"
        :disabled="disabled"
        :disable="disabledTimes"
        :displayFormat="'HH[h]mm'"
        :value="timeValue" @input="emitChangeTime" />
    </b-col>
  </b-row>
</template>

<script>
import Timeselector from 'vue-timeselector';

import FormsDatePicker from '@/components/Forms/DatePicker.vue';

export default {
  name: 'FormsDateTimePicker',
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
    disabledTimes: {
      type: Object,
      required: false,
      default() {
        return {};
      },
    },
    value: {
      type: String,
      required: true,
    },
  },
  components: {
    FormsDatePicker,
    Timeselector,
  },
  computed: {
    dateValue() {
      return this.parts[0];
    },
    timeValue() {
      return this.$dayjs(this.value).toDate();
    },
    parts() {
      return this.value.split(' ');
    },
  },
  methods: {
    emitChangeDate(value) {
      this.$emit('input', `${value} ${this.parts[1]}`);
    },
    emitChangeTime(value) {
      const newTime = this.$dayjs(value).format('HH:mm');
      this.$emit('input', `${this.parts[0]} ${newTime}:00`);
    },
  },
};
</script>

<style lang="scss">
.forms-date-time-picker {
  .forms-datepicker {
    margin-bottom: 10px;
  }

  .vtimeselector__clear {
    display: none;
  }
}
</style>
