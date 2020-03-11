<template>
  <div class="forms-date-time-picker">
    <forms-date-picker
      :disabled-dates="disabledDates"
      :value="dateValue"
      @input="emitChangeDate" />

    <timeselector :h24="true"
      :disable="disabledTimes"
      :displayFormat="'HH[h]mm'"
      :value="timeValue" @input="emitChangeTime" />
  </div>
</template>

<script>
import Timeselector from 'vue-timeselector';

import FormsDatePicker from '@/components/Forms/DatePicker.vue';

export default {
  name: 'FormsDateTimePicker',
  props: {
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
}
</style>
