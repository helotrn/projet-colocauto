export default {
  namespaced: true,
  state: {
    lastLoan: {},
    selectedLoanableTypes: ['bike', 'trailer', 'car'],
  },
  mutations: {
    lastLoan(state, lastLoan) {
      state.lastLoan = lastLoan;
    },
    selectedLoanableTypes(state, selectedLoanableTypes) {
      state.selectedLoanableTypes = selectedLoanableTypes;
    },
  },
};
