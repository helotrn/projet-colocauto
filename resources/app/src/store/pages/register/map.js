export default {
  namespaced: true,
  state: {
    borough: null,
    center: null,
    community: null,
    postalCode: "",
  },
  mutations: {
    borough(state, value) {
      state.borough = value;
    },
    center(state, value) {
      state.center = value;
    },
    community(state, value) {
      state.community = value;
    },
    postalCode(state, value) {
      state.postalCode = value;
    },
  },
};
