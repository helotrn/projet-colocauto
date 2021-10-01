import Vue from "vue";

export default {
  namespaced: true,
  state: {
    data: {},
    error: null,
    lastLoadedAt: null,
    loaded: false,
  },
  mutations: {
    data(state, data) {
      state.data = data;
    },
    error(state, error) {
      state.error = error;
    },
    lastLoadedAt(state, lastLoadedAt) {
      state.lastLoadedAt = lastLoadedAt;
    },
    loaded(state, loaded) {
      state.loaded = loaded;
    },
  },
  actions: {
    async retrieve({ commit, state }) {
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();

      if (state.lastLoadedAt < Date.now() - 3600) {
        commit("loaded", false);

        try {
          commit("cancelToken", cancelToken);
          const response = await Vue.axios.get("/stats", { cancelToken });

          commit("data", response.data);
          commit("lastLoadedAt", Date.now());

          commit("loaded", true);

          commit("cancelToken", null);
        } catch (e) {
          commit("cancelToken", null);
          commit("error", e.response.data);

          throw e;
        }
      }
    },
  },
};
