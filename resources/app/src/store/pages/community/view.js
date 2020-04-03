export default {
  namespaced: true,
  state: {
    center: null,
    lastLoan: {},
    selectedLoanableTypes: ['bike', 'trailer', 'car'],
  },
  mutations: {
    center(state, center) {
      state.center = center;
    },
    lastLoan(state, lastLoan) {
      state.lastLoan = lastLoan;
    },
    selectedLoanableTypes(state, selectedLoanableTypes) {
      state.selectedLoanableTypes = selectedLoanableTypes;
    },
  },
};
