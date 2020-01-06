export default {
  namespaced: true,
  state: {
    ajax: null,
    error: null,
    email: '',
    loading: false,
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
