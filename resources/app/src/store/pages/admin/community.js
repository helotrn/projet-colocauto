export default {
  namespaced: true,
  state: {
    usersFilter: "",
  },
  mutations: {
    usersFilter(state, value) {
      state.usersFilter = value;
    },
  },
};
