import Vue from "vue";

export default {
  namespaced: true,
  state: {
    ajax: null,
    data: {},
    error: null,
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
  },
  actions: {
    async request({ commit }, request) {
      try {
        const ajax = Vue.axios.post("/auth/password/request", request);

        commit("ajax", ajax);

        const { data } = await ajax;

        commit("data", data);

        commit("ajax", null);
      } catch (e) {
        commit("ajax", null);

        commit("error", e.response.data);

        throw e;
      }
    },
    async reset({ commit }, { email, newPassword, newPasswordRepeat, token }) {
      try {
        const ajax = Vue.axios.post("/auth/password/reset", {
          email,
          password: newPassword,
          password_confirmation: newPasswordRepeat,
          token,
        });

        commit("ajax", ajax);

        const { data } = await ajax;

        commit("data", data);

        commit("ajax", null);
      } catch (e) {
        commit("ajax", null);

        commit("error", e.response.data);

        throw e;
      }
    },
  },
};
