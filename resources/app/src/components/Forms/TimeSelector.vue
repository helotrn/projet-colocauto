<template>
  <b-form-select class="time-selector" :disabled="disabled" v-model="selected" :options="timeslots" />
</template>

<script lang="ts">
import Vue from "vue";
import Component from "vue-class-component";
import dayjs from "dayjs";
import utc from "dayjs/plugin/utc";
import { Prop, Watch } from "vue-property-decorator";

const MILLISECONDS_IN_A_DAY = 24 * 60 * 60 * 1000;
const MILLISECONDS_IN_A_MINUTE = 60 * 1000;

dayjs.extend(utc);

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

  @Prop({
    type: Object,
    default: () => ({ h: [], m: [], s: [] }),
    validator: (value: any) => {
      const allArrays = Array.isArray(value.h) && Array.isArray(value.h) && Array.isArray(value.s);

      if (!allArrays) {
        return false;
      }

      if (
        !value.h.every((item: any) => typeof item === "number") ||
        !value.m.every((item: any) => typeof item === "number") ||
        !value.s.every((item: any) => typeof item === "number")
      ) {
        return false;
      }

      return true;
    },
  })
  disabledTimes!: {
    h: Array<number>;
    m: Array<number>;
    s: Array<number>;
  };

  get allTimeSlotsInTypicalDay() {
    const millisecondsInInterval = this.minuteInterval * 60 * 1000;

    return new Array(MILLISECONDS_IN_A_DAY / millisecondsInInterval).fill(null).map((v, idx) => {
      return dayjs(idx * millisecondsInInterval).utc();
    });
  }

  get timeslots(): Option[] {
    return this.allTimeSlotsInTypicalDay
      .map((timeOfDay) => {
        const dayOfValue = dayjs(this.value);

        return dayOfValue.set("hour", timeOfDay.hour()).set("minute", timeOfDay.minute());
      })
      .map((timeOfDayAtValue) => {
        return {
          value: timeOfDayAtValue.toDate(),
          text: timeOfDayAtValue.format("HH:mm"),
          disabled: false,
        };
      })
      .map((option) => {
        if (this.excludePastTime && option.value < dayjs().toDate()) {
          option.disabled = true;
        }

        return option;
      })
      .map((option) => {
        const dValue = dayjs(option.value);

        if (
          this.disabledTimes.h.includes(dValue.hour()) ||
          this.disabledTimes.m.includes(dValue.minute())
        ) {
          option.disabled = true;
        }

        return option;
      });
  }

  closest(needle: Date) {
    const haystack = this.timeslots
      .filter((option) => option.disabled !== true)
      .map(({ value }) => value);

    return haystack.reduce((a, b) => {
      let aDiff = Math.abs(a.getTime() - needle.getTime());
      let bDiff = Math.abs(b.getTime() - needle.getTime());

      if (aDiff == bDiff) {
        return a > b ? a : b;
      } else {
        return bDiff < aDiff ? b : a;
      }
    });
  }

  selected: Date | null = null;

  @Watch("timeslots")
  onOptionsChanged() {
    const dayOfValue = dayjs(this.value);

    const selectedOnValueDate = dayjs(this.selected)
      .set("year", dayOfValue.year())
      .set("month", dayOfValue.month())
      .set("date", dayOfValue.date());

    this.selected = this.closest(selectedOnValueDate.toDate());
  }

  created() {
    this.selected = this.closest(this.value);

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

.time-selector {
  option {
    @extend .monospace;
  }
}
</style>
