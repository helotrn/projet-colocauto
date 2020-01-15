export default {
  namespaced: true,
  state: {
    center: null,
    community: null,
    postalCode: '',
  },
  mutations: {
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
