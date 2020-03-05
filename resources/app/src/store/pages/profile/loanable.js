export default {
  namespaced: true,
  state: {
    center: null,
  },
  mutations: {
    center(state, value) {
      state.center = value;
    },
  },
};
