import dayjs from "@/helpers/dayjs";

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
  methods: {
    /*
       This is generic and may be made available outside of loans.
       It expects a dayjs or Date object.
    */
    dateIsInThePast: (date) => {
      // Return whether the start of next day is in the past, meaning that the
      // whole day is in the past.
      return dayjs(date).startOfDay().add(1, "day").isSameOrBefore(dayjs());
    },
    /*
       This is generic and may be made available outside of loans.
       It expects a dayjs or Date object.
    */
    timeIsInThePast: (time) => {
      return dayjs(time).isSameOrBefore(dayjs());
    },
  },
  computed: {
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
