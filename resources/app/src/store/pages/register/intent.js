export default {
  namespaced: true,
  state: {
    carIntent: false,
    ownerIntent: false,
  },
  mutations: {
    carIntent(state, value) {
      state.carIntent = value;
    },
    ownerIntent(state, value) {
      state.ownerIntent = value;
    },
  },
};
