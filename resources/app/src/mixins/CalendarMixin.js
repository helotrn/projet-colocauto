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
        id: loan.uri.replace('/loans/', ''),
        data:{
          departure_at: this.$dayjs(newDates.start).format("YYYY-MM-DD HH:mm:ss"),
          duration_in_minutes: this.$dayjs(newDates.end).diff(newDates.start, 'minutes'),
        },
        params: {
          fields: 'departure_at, actual_return_at, status, borrower.user.full_name,'
          +' borrower.user.avatar, borrower.user.email, borrower.user.phone',
        },
      });
    },
    // revert the event back to its original value
    restoreEventDisplay(updatedEventUri, eventsList) {
      let originalIndex = eventsList.findIndex(e => e.uri == updatedEventUri);
      let originalEvent = eventsList[originalIndex];
      eventsList.splice(originalIndex, 1);
      eventsList.push(originalEvent);
    },
    updateEventDisplay(updatedEvent, eventsList) {
      let originalIndex = eventsList.findIndex(e => e.uri == updatedEvent.uri);
      let originalEvent = eventsList[originalIndex];
      eventsList.splice(originalIndex, 1);
      originalEvent.data = updatedEvent.data;
      originalEvent.start = originalEvent.data.departure_at;
      originalEvent.end = originalEvent.data.actual_return_at;
      eventsList.push(originalEvent);
    },
    async testLoan(start, end, loanable_id){
      // test if this loan is possible
      await this.$store.dispatch("loans/test", {
        departure_at: this.$dayjs(start).format("YYYY-MM-DD HH:mm:ss"),
        duration_in_minutes: this.$dayjs(end).diff(start, 'minutes'),
        estimated_distance:10,
        loanable_id,
      });
    }
  }
}
