export default {
  props: {
    form: {
      type: Object,
      required: false,
      default() {
        return null;
      },
    },
    item: {
      type: Object,
      required: true,
    },
  },
  computed: {
    disabledDates() {
      return {
        to: this.$dayjs(this.item.departure_at).subtract(1, "day").toDate(),
      };
    },
    disabledDatesInThePast() {
      return {
        to: this.$dayjs().subtract(1, "day").toDate(),
      };
    },
    disabledTimes() {
      const departure = this.$dayjs(this.item.departure_at);
      if (departure.format("YYYY-MM-DD") < this.$dayjs(this.returnAt).format("YYYY-MM-DD")) {
        return {};
      }

      const hours = [];
      const departureHour = this.$dayjs(this.item.departure_at).hour();
      for (let i = departureHour - 1; i >= 0; i -= 1) {
        hours.push(i);
      }

      const minutes = [];
      if (this.$dayjs(this.returnAt).hour() === departureHour) {
        for (let i = this.$dayjs(this.item.departure_at).minute(); i >= 0; i -= 1) {
          minutes.push(i);
        }
      }

      return {
        h: hours,
        m: minutes,
        s: [],
      };
    },
    disabledTimesInThePast() {
      const departure = this.$dayjs(this.item.departure_at);

      if (departure.format("YYYY-MM-DD") > this.$dayjs().format("YYYY-MM-DD")) {
        return {};
      }

      const hours = [];
      const nowHour = this.$dayjs().hour();
      for (let i = nowHour - 1; i >= 0; i -= 1) {
        hours.push(i);
      }

      const minutes = [];
      if (departure.hour() === nowHour) {
        for (let i = this.$dayjs().minute(); i >= 0; i -= 1) {
          minutes.push(i);
        }
      }

      return {
        h: hours,
        m: minutes,
        s: [],
      };
    },
    returnAt: {
      get() {
        return this.$dayjs(this.item.departure_at)
          .add(this.item.duration_in_minutes, "minute")
          .format("YYYY-MM-DD HH:mm:ss");
      },
      set(val) {
        this.item.duration_in_minutes = this.$dayjs(val).diff(
          this.$dayjs(this.item.departure_at),
          "minute"
        );
      },
    },
  },
  watch: {
    item: {
      deep: true,
      handler(item) {
        this.$store.commit("loans/item", item);
      },
    },
  },
};
