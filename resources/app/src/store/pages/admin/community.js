import Vue from "vue";

export default {
  namespaced: true,
  state: {
    communityUserListParams: {
      page: 1,
      order: "",
      filters: {
        user_id: "",
        user_full_name: "",
      },
    },
  },
  mutations: {
    communityUserListParam(state, { name, value }) {
      // Vue.set ensures reactivity of the view to the property.
      Vue.set(state.communityUserListParams, name, value);

      // Reset pagination if filters are changed.
      if ("filters" === name) {
        Vue.set(state.communityUserListParams, "page", 1);
      }
    },
  },
};
