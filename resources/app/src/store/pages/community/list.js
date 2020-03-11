export default {
  namespaced: true,
  state: {
    loanLoaded: false,
    loan: {},
    selectedLoanableTypes: [],
  },
  mutations: {
    loan(state, loan) {
      state.loan = loan;
    },
    loanLoaded(state, loanLoaded) {
      state.loanLoaded = loanLoaded;
    },
    selectedLoanableTypes(state, selectedLoanableTypes) {
      state.selectedLoanableTypes = selectedLoanableTypes;
    },
  },
};
