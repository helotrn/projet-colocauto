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
  data() {
    return {
      estimatedDistanceStr: "",
    };
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
    formattedEstimatedDistance: {
      get() {
        // if the estimated distance is 0, there will be nothing displayed
        if(this.item.estimated_distance === 0)
          return "";

        // if no input has been provided yet, the initial value of estimated distance will be displayed
        if(!this.estimatedDistanceStr)
          return this.item.estimated_distance;

        // display the input value as a string
        return this.estimatedDistanceStr;
      },
      set(val) {
        // set the estimated distance to be displayed to the input value.
        this.estimatedDistanceStr = val;
        
        // if the input is a number, set the estimated distance to the value unchanged.
        if(typeof(val) === "number")
          this.item.estimated_distance = val;
        
        // if the input is empty, set the estimated distance to 0.
        else if(val === "") {
          this.item.estimated_distance = 0;
        }
        
        // if the input is not a number, parse it into a float and round it to an int.
        else {
          const valueFloat = parseFloat(val.replace(",", "."));
          const roundedValue = Math.round(valueFloat);
          
          // set the estimated distance to the rounded value.
          this.item.estimated_distance = roundedValue;
        }
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
