import Vue from "vue";

export default {
  namespaced: true,
  state: {
    errors: null,
  },
  mutations: {
    errors(state, errors) {
      state.errors = errors;
    },
  },
  actions: {
    async upload({ commit }, formData) {
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();
      commit("errors", null);

      try {
        commit("cancelToken", cancelToken);
        const response = await Vue.axios.post("/files", formData);

        commit("cancelToken", null);

        return file;
      } catch (e) {
        commit("errors", e.response.data);

        commit("cancelToken", null);

        return null;
      }
    },
  },
};
