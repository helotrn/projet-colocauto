export default {
  namespaced: true,
  state: {
    loan: {
      departure_at: null,
      duration_in_minutes: 60,
    },
    selectedLoanableTypes: ['car', 'bike', 'trailer'],
  },
  mutations: {
    loan(state, loan) {
      state.loan = loan;
    },
    selectedLoanableTypes(state, selectedLoanableTypes) {
      state.selectedLoanableTypes = selectedLoanableTypes;
    },
  },
};
