export default {
  namespaced: true,
  state: {
    borough: null,
    neighborhood: null,
    center: null,
  },
  mutations: {
    borough(state, value) {
      state.borough = value;
    },
    neighborhood(state, value) {
      state.neighborhood = value;
    },
    center(state, value) {
      state.center = value;
    },
  },
};
