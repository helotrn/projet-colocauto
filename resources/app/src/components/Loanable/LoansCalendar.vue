<template>
  <loanable-calendar
    defaultView="week"
    :events="availability"
    variant="small"
    @ready="getAvailability"
    @view-change="getAvailability"
    :loanable="loanable"
  ></loanable-calendar>
</template>

<script>
import Vue from "vue";
import LoanableCalendar from "@/components/Loanable/Calendar.vue";

export default {
  name: "LoansCalendar",
  components: {
    LoanableCalendar,
  },
  data() {
    return {
      availability: [],
    }
  },
  props: {
    loanable: {
      type: Object,
      required: true,
    },
  },
  methods: {
    getAvailability({ view, startDate, endDate, firstCellDate, lastCellDate, week }) {
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();

      let start, end;

      // Include out-of-scope days in month view.
      if (view === "month") {
        // Must convert [, ] interval to [, ) by adding one second to the end time.
        start = this.$dayjs(firstCellDate);
        end = this.$dayjs(lastCellDate).add(1, "s");
      } else {
        // Must convert [, ] interval to [, ) by adding one second to the end time.
        start = this.$dayjs(startDate);
        end = this.$dayjs(endDate).add(1, "s");
      }

      try {
        Vue.axios
          .get(`/loanables/${this.loanable.id}/availability`, {
            params: {
              start: start.format("YYYY-MM-DD HH:mm:ss"),
              end: end.format("YYYY-MM-DD HH:mm:ss"),
              responseMode: "loans",
            },
            cancelToken: cancelToken.token,
          }).then(response => {
            this.availability = response.data
          });
      } catch (e) {
        throw e;
      }
    },
  },
};
</script>
