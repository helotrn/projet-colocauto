<template>
  <b-form-select
    class="time-selector"
    :disabled="disabled"
    :disabled-times-fct="disabledTimesFct"
    v-model="selected"
    :options="timeslots"
  />
</template>

<script lang="ts">
import Vue from "vue";
import Component from "vue-class-component";
import dayjs from "../../helpers/dayjs";
import { Prop, Watch } from "vue-property-decorator";

const MILLISECONDS_IN_A_DAY = 24 * 60 * 60 * 1000;
const MILLISECONDS_IN_A_MINUTE = 60 * 1000;

interface Option {
  value: Date;
  text: string;
  disabled?: boolean;
}

@Component
export default class TimeSelector extends Vue {
  @Prop({ type: Date, default: () => new Date() })
  value!: Date;

  @Prop({
    type: Number,
    default: () => 15,
    validator: (value: number) => {
      if (MILLISECONDS_IN_A_DAY % (value * MILLISECONDS_IN_A_MINUTE) !== 0) {
        console.error(
          `Invalid prop in \`minuteInterval\` in TimeSelector: 24 hours should be divisible by this number of minutes`
        );
      }

      return true;
    },
  })
  minuteInterval!: number;

  @Prop({ type: Boolean, default: false })
  disabled!: boolean;

  @Prop({ type: Boolean, default: false })
  excludePastTime!: boolean;

  /*
     disabledTimesFct(time) { return true|false; };
  */
  @Prop({ type: Function, default: () => false })
  disabledTimesFct;

  get timeslots(): Option[] {
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

        // Disable past times.
        .map((option) => {
          if (this.excludePastTime && option.time.isSameOrBefore(now)) {
            option.disabled = true;
          }

          return option;
        })
    );
  }

  allDayTimeSlots(date: Date) {
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
  }

  closestOption(needle: Date) {
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
  }

  selected: Date | null = null;

  @Watch("timeslots")
  onOptionsChanged() {
    const dayOfValue = dayjs(this.value);

    const selectedOnValueDate = dayjs(this.selected)
      .set("year", dayOfValue.year())
      .set("month", dayOfValue.month())
      .set("date", dayOfValue.date());

    const { value } = this.closestOption(selectedOnValueDate.toDate());
    this.selected = value;
  }

  created() {
    const { value } = this.closestOption(this.value);
    this.selected = value;

    this.onSelection();
  }

  @Watch("selected")
  onSelection() {
    this.$emit("input", this.selected);
  }
}
</script>

<style lang="scss">
@import "~/node_modules/bootstrap";
@import "@/assets/scss/_typography.scss";
</style>
