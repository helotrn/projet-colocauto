<template>
  <div class="date-range-picker">
    <date-picker v-model="from" clear-button />
    <span>:</span>
    <date-picker v-model="to" clear-button />
  </div>
</template>

<script>
import DatePicker from "@/components/Forms/DatePicker.vue";
import dayjs from "dayjs";
import timeZone from "dayjs/plugin/timezone";
import utc from "dayjs/plugin/utc";
dayjs.extend(timeZone);
dayjs.extend(utc);

export default {
  name: "FormsDateRangePicker",
  components: {
    DatePicker,
  },
  props: {
    value: {
      required: false,
      type: String,
      default: ":",
    },
  },
  computed: {
    from: {
      get() {
        if (!this.value || this.value === ":") {
          return null;
        }

        return this.value.match(/(.*?)T.*@/)
          ? dayjs.tz(this.value.match(/(.*?)T.*@/)[1])
              .startOf('day')
              .format('YYYY-MM-DDT00:00:00[Z]')
          : null;
      },
      set(val) {
        if (val || this.to) {
          this.$emit(
            "input",
            `${val ? dayjs.tz(val).format('YYYY-MM-DDT00:00:00[Z]') : ""}@${
              this.to ? dayjs.tz(this.to).endOf('day').toISOString() : ""
            }`
          );
        } else {
          this.$emit("input", "");
        }
      },
    },
    to: {
      get() {
        if (!this.value || this.value === ":") {
          return null;
        }
        return this.value.match(/.*@(.*?)T/)
          ? dayjs.tz(this.value.match(/.*@(.*?)T/)[1])
              .endOf('day')
              .toISOString()
          : null;
      },
      set(val) {
        if (this.from || val) {
          this.$emit(
            "input",
            `${this.from ? dayjs.tz(this.from).format('YYYY-MM-DDT00:00:00[Z]') : ""}@${
              val ? dayjs.tz(val).endOf('day').toISOString() : ""
            }`
          );
        } else {
          this.$emit("input", "");
        }
      },
    },
  },
};
</script>

<style lang="scss">
.date-range-picker {
  display: flex;

  > span {
    flex-shrink: 1;
    margin: auto 8px;
  }
}
</style>
