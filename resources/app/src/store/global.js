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
    ajax(state, ajax) {
      state.ajax = ajax;
    },
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
      if (!state.loaded) {
        try {
          const ajax = Vue.axios.get("/tags");

          commit("ajax", ajax);

          const {
            data: { data: tags },
          } = await ajax;

          commit("tags", tags);
          commit("lastLoadedAt", Date.now());

          commit("loaded", true);

          commit("ajax", null);
        } catch (e) {
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
