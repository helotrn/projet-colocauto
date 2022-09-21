export default {
  namespaced: true,
  state: {
    email: "",
  },
  mutations: {
    email(state, value) {
      state.email = value;
    },
  },
};
