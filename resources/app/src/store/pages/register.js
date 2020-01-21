export default {
  namespaced: true,
  state: {
    email: '',
    loading: false
  },
  mutations: {
    email(state, value) {
      state.email = value;
    },
    loading(state, value) {
      state.loading = value;
    }
  }
};
