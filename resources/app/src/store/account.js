import Vue from "vue";

export default {
  namespaced: true,
  state: {
    data: null,
    error: null,
    loaded: false,
    transactionId: 1,
  },
  mutations: {
    data(state, data) {
      state.data = data;
    },
    error(state, error) {
      state.error = error;
    },
    transactionId(state, transactionId) {
      state.transactionId = transactionId;
    },
    loaded(state, loaded) {
      state.loaded = loaded;
    },
    cancelToken(state, cancelToken) {
      state.cancelToken = cancelToken;
    },
  },
  actions: {
    async buyCredit({ commit, state }, { amount, paymentMethodId }) {
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();
      commit("loaded", false);

      try {
        commit("cancelToken", cancelToken);
        const { data } = await Vue.axios.put(
          "/auth/user/balance",
          {
            amount,
            payment_method_id: paymentMethodId,
            transaction_id: state.transactionId,
          },
          { cancelToken: cancelToken.token }
        );

        commit("data", data);
        commit("loaded", true);
        commit("cancelToken", null);
      } catch (e) {
        commit("cancelToken", null);
        commit("error", e.response.data);

        throw e;
      }
    },
    async claimCredit({ commit }) {
      commit("loaded", false);
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();

      commit();
      try {
        commit("cancelToken", cancelToken);
        const { data } = await Vue.axios.put("/auth/user/claim", null, {
          cancelToken: cancelToken.token,
        });
        commit("data", data);

        commit("loaded", true);
        commit("cancelToken", null);
      } catch (e) {
        commit("cancelToken", null);

        commit("error", e.response.data);

        throw e;
      }
    },
    async mandate({}, { mandatedUserId }) {
      const response = await Vue.axios(`/auth/password/mandate/${mandatedUserId}`);
      location.href = location.href.match(/http?.:\/\/.*?\//)[0];
      localStorage.setItem("locomotion", JSON.stringify({ token: response.data }));

      // If we reload right now we are redirected to the login page
      setTimeout(() => {
        location.reload();
      }, 500);
    },
  },
};
