import Vue from "vue";

export default {
  namespaced: true,
  state: {
    ajax: null,
    data: null,
    error: null,
    loaded: false,
    transactionId: 1,
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
    transactionId(state, transactionId) {
      state.transactionId = transactionId;
    },
    loaded(state, loaded) {
      state.loaded = loaded;
    },
  },
  actions: {
    async buyCredit({ commit, state }, { amount, paymentMethodId }) {
      commit("loaded", false);

      try {
        const ajax = Vue.axios.put("/auth/user/balance", {
          amount,
          payment_method_id: paymentMethodId,
          transaction_id: state.transactionId,
        });

        commit("ajax", ajax);

        const { data } = await ajax;

        commit("data", data);

        commit("loaded", true);

        commit("ajax", null);
      } catch (e) {
        commit("ajax", null);

        commit("error", e.response.data);

        throw e;
      }
    },
    async claimCredit({ commit }) {
      commit("loaded", false);

      try {
        const ajax = Vue.axios.put("/auth/user/claim");

        commit("ajax", ajax);

        const { data } = await ajax;

        commit("data", data);

        commit("loaded", true);

        commit("ajax", null);
      } catch (e) {
        commit("ajax", null);

        commit("error", e.response.data);

        throw e;
      }
    },
  },
};
