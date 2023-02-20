import Vue from "vue";

export default {
  methods: {
    async getLoanDetails(event){
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();

      return Vue.axios
        .get(event.uri, {
          params: {
            fields: 'departure_at, actual_return_at, status, borrower.user.full_name,'
            +' borrower.user.avatar, borrower.user.email, borrower.user.phone',
          },
          cancelToken: cancelToken.token,
        })
        .then(response => {
          if(response.status == 200) {
            return response.data
          }
        });
    },
    updateLoanDates(loan, newDates){
      return this.$store.dispatch("loans/update", {
        id: loan.uri.replace('/loans', ''),
        data:{
          departure_at: this.$dayjs(newDates.start).format("YYYY-MM-DD HH:mm:ss"),
          duration_in_minutes: this.$dayjs(newDates.end).diff(newDates.start, 'minutes'),
        },
        params: {
          fields: 'departure_at, actual_return_at, status, borrower.user.full_name,'
          +' borrower.user.avatar, borrower.user.email, borrower.user.phone',
        },
      });
    }
  }
}
