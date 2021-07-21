import Vue from "vue";

export default {
  namespaced: true,
  state: {
    ajax: null,
    data: {},
    error: null,
    lastLoadedAt: null,
    loaded: false,
  },
  mutations: {
    ajax(state, ajax) {
      state.ajax = ajax;
    },
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
      if (state.lastLoadedAt < Date.now() - 3600) {
        commit("loaded", false);

        try {
          const ajax = Vue.axios.get("/stats");

          commit("ajax", ajax);

          const { data } = await ajax;

          commit("data", data);
          commit("lastLoadedAt", Date.now());

          commit("loaded", true);

          commit("ajax", null);
        } catch (e) {
          commit("ajax", null);

          commit("error", e.response.data);

          throw e;
        }
      }
    },
  },
};
