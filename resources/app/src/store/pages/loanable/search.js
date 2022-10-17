export default {
  namespaced: true,
  state: {
    center: null,
    lastLoan: {},
    searched: false,
    selectedLoanableTypes: ["bike", "trailer", "car"],
  },
  mutations: {
    center(state, center) {
      state.center = center;
    },
    lastLoan(state, lastLoan) {
      state.lastLoan = lastLoan;
    },
    searched(state, searched) {
      state.searched = searched;
    },
    selectedLoanableTypes(state, selectedLoanableTypes) {
      state.selectedLoanableTypes = selectedLoanableTypes;
    },
  },
};
