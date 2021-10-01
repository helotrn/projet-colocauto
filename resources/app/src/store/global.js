import Vue from "vue";

export default {
  namespaced: true,
  state: {
    error: null,
    lastLoadedAt: null,
    loaded: false,
    tags: [],
  },
  mutations: {
    lastLoadedAt(state, lastLoadedAt) {
      state.lastLoadedAt = lastLoadedAt;
    },
    loaded(state, loaded) {
      state.loaded = loaded;
    },
    tags(state, tags) {
      state.tags = tags;
    },
  },
  actions: {
    async load({ commit, state }) {
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();

      if (!state.loaded) {
        try {
          commit("cancelToken", cancelToken);
          const response = await Vue.axios.get("/tags", { cancelToken });

          commit("tags", response.data);
          commit("lastLoadedAt", Date.now());

          commit("loaded", true);

          commit("cancelToken", null);
        } catch (e) {
          commit("cancelToken", null);
          throw e;
        }
      }
    },
    async reload({ commit, dispatch }) {
      commit("loaded", false);
      await dispatch("load");
    },
  },
};
