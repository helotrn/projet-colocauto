<template>
  <b-form-select
    class="time-selector"
    :disabled="disabled"
    :disabled-times-fct="disabledTimesFct"
    v-model="selected"
    :options="timeslots"
  />
</template>

<script>
import Vue from "vue";
import Component from "vue-class-component";
import dayjs from "../../helpers/dayjs";
import { Prop, Watch } from "vue-property-decorator";

const MILLISECONDS_IN_A_DAY = 24 * 60 * 60 * 1000;
const MILLISECONDS_IN_A_MINUTE = 60 * 1000;

export default {
  name: "TimeSelector",

  props: {
    value: {
      type: Date,
      required: false,
      default: () => new Date(),
    },

    minuteInterval: {
      type: Number,
      required: false,
      default: () => 15,
      validator: (value) => {
        if (MILLISECONDS_IN_A_DAY % (value * MILLISECONDS_IN_A_MINUTE) !== 0) {
          console.error(
            `Invalid prop in \`minuteInterval\` in TimeSelector: 24 hours should be divisible by this number of minutes`
          );
        }
        return true;
      },
    },

    disabled: {
      type: Boolean,
      required: false,
      default: false,
    },

    // disabledTimesFct(time) { return true|false; };
    disabledTimesFct: {
      type: Function,
      required: false,
      default: () => false,
    },
  },

  computed: {
    timeslots() {
      // Compute "now" once so it won't change along the way.
      const now = dayjs();

      return (
        this.allDayTimeSlots(dayjs(this.value))
          // Augment with option properties.
          .map((timeOfDayAtValue) => {
            return {
              // Carry time along options so as to perform computations on source objects.
              time: timeOfDayAtValue,
              value: timeOfDayAtValue.toDate(),
              text: timeOfDayAtValue.format("HH:mm"),
              disabled: false,
            };
          })

          // Disable options from calling disabledTimesFct.
          .map((option) => {
            if (this.disabledTimesFct && this.disabledTimesFct(option.time)) {
              option.disabled = true;
            }
            return option;
          })
      );
    },
  },

  methods: {
    allDayTimeSlots(date) {
      const millisecondsInInterval = this.minuteInterval * 60 * 1000;

      // Ensure date is at the start of the day
      date = date.startOfDay();

      // Fill a new array with the correct number...
      const allTimeSlots = new Array(MILLISECONDS_IN_A_DAY / millisecondsInInterval)
        // ... of empty time slots...
        .fill(null)
        // ... then set them to the current date and appropriate time.
        .map((v, idx) => {
          return date.add(idx * millisecondsInInterval, "millisecond");
        });

      return allTimeSlots;
    },

    closestOption(needle) {
      return this.timeslots.reduce((closest, current) => {
        // Set closest to current item on first iteration.
        if (!closest) {
          return current;
        }

        let diffClosest = Math.abs(closest.value.getTime() - needle.getTime());
        let diffCurrent = Math.abs(current.value.getTime() - needle.getTime());

        if (diffClosest == diffCurrent) {
          return closest.value > current.value ? closest : current;
        } else {
          return diffCurrent < diffClosest ? current : closest;
        }
      }, undefined);
    },
  },

  watch: {
    timeslots: function () {
      const dayOfValue = dayjs(this.value);

      const selectedOnValueDate = dayjs(this.selected)
        .set("year", dayOfValue.year())
        .set("month", dayOfValue.month())
        .set("date", dayOfValue.date());

      const { value } = this.closestOption(selectedOnValueDate.toDate());
      this.selected = value;
    },

    selected: function () {
      this.$emit("input", this.selected);
    },
  },

  created() {
    const { value } = this.closestOption(this.value);
    this.selected = value;
  },
};
</script>

<style lang="scss">
@import "~/node_modules/bootstrap";
@import "@/assets/scss/_typography.scss";
</style>
