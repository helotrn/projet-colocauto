export default {
  namespaced: true,
  state: {
    email: "",
    loading: false,
    rememberMe: false,
  },
  mutations: {
    email(state, value) {
      state.email = value;
    },
    loading(state, value) {
      state.loading = value;
    },
    rememberMe(state, value) {
      state.rememberMe = value;
    },
  },
};
