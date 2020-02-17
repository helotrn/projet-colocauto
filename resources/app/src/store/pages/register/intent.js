export default {
  namespaced: true,
  state: {
    borrower: {},
    carBorrowerIntent: false,
    ownerIntent: false,
  },
  mutations: {
    borrower(state, borrower) {
      state.borrower = borrower;
    },
    carBorrowerIntent(state, value) {
      state.carBorrowerIntent = value;
    },
    ownerIntent(state, value) {
      state.ownerIntent = value;
    },
  },
};
