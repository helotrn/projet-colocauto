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
    cancelToken(state, cancelToken) {
      if (cancelToken && state.cancelToken) {
        state.cancelToken.cancel(`${state.slug} canceled`);
      }
      state.cancelToken = cancelToken;
    },
  },
  actions: {
    async upload({ commit }, formData) {
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();

      commit("errors", null);

      try {
        commit("cancelToken", cancelToken);
        const { data: image } = await Vue.axios.post("/images", formData, {
          cancelToken: cancelToken.token,
        });

        commit("cancelToken", null);

        return image;
      } catch (e) {
        commit("errors", e.response.data);

        commit("cancelToken", null);

        return null;
      }
    },
  },
};
