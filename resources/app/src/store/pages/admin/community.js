import Vue from "vue";

export default {
  namespaced: true,
  state: {
    usersFilter: "",
    communityUserListParams: {
      page: 1,
      order: "",
    },
  },
  mutations: {
    usersFilter(state, value) {
      state.usersFilter = value;
    },
    communityUserListParam(state, { name, value }) {
      // Vue.set ensures reactivity of the view to the property.
      Vue.set(state.communityUserListParams, name, value);
    },
  },
};
