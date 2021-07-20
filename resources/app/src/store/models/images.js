import Vue from "vue";

export default {
  namespaced: true,
  state: {
    ajax: false,
    errors: null,
  },
  mutations: {
    ajax(state, ajax) {
      state.ajax = ajax;
    },
    errors(state, errors) {
      state.errors = errors;
    },
  },
  actions: {
    async upload({ commit }, formData) {
      commit("errors", null);

      try {
        const ajax = Vue.axios.post("/images", formData);

        commit("ajax", ajax);

        const { data: image } = await ajax;

        commit("ajax", null);

        return image;
      } catch (e) {
        commit("errors", e.response.data);

        commit("ajax", null);

        return null;
      }
    },
  },
};
