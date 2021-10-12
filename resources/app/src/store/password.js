import Vue from "vue";

export default {
  namespaced: true,
  state: {
    data: {},
    error: null,
  },
  mutations: {
    data(state, data) {
      state.data = data;
    },
    error(state, error) {
      state.error = error;
    },
    cancelToken(state, cancelToken) {
      state.cancelToken = cancelToken;
    },
  },
  actions: {
    async request({ commit }, request) {
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();

      try {
        commit("cancelToken", cancelToken);
        const { data } = await Vue.axios.post("/auth/password/request", request, {
          cancelToken: cancelToken.token,
        });

        commit("data", data);

        commit("cancelToken", null);
      } catch (e) {
        commit("cancelToken", null);

        commit("error", e.response.data);

        throw e;
      }
    },
    async reset({ commit }, { email, newPassword, newPasswordRepeat, token }) {
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();

      try {
        const { data } = await Vue.axios.post(
          "/auth/password/reset",
          {
            email,
            password: newPassword,
            password_confirmation: newPasswordRepeat,
            token,
          },
          { cancelToken: cancelToken.token }
        );

        commit("cancelToken", cancelToken);

        commit("data", data);

        commit("cancelToken", null);
      } catch (e) {
        commit("cancelToken", null);

        commit("error", e.response.data);

        throw e;
      }
    },
  },
};
