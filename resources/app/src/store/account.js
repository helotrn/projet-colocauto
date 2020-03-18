import Vue from 'vue';

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
    incrementTransactionId(state) {
      state.transactionId += 1;
    },
    transactionId(state, transactionId) {
      state.transactionId = transactionId;
    },
    loaded(state, loaded) {
      state.loaded = loaded;
    },
  },
  actions: {
    async buyCredit({ commit, state }, amount) {
      commit('loaded', false);

      try {
        const ajax = Vue.axios.put('/auth/user/balance', {
          amount,
          transaction_id: state.transactionId,
        });

        commit('ajax', ajax);

        const { data } = await ajax;

        commit('data', data);

        commit('loaded', true);

        commit('ajax', null);

        commit('incrementTransactionId', null);
      } catch (e) {
        commit('ajax', null);

        commit('error', e.response.data);

        throw e;
      }
    },
  },
};
