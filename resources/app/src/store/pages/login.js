export default {
  namespaced: true,
  state: {
    email: "",
    rememberMe: false,
  },
  mutations: {
    email(state, value) {
      state.email = value;
    },
    rememberMe(state, value) {
      state.rememberMe = value;
    },
  },
};
