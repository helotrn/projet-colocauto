import Vue from 'vue';
import RestModule from '../RestModule';

export default new RestModule('loans', {
  params: {
    order: '-departure_at',
    page: 1,
    per_page: 10,
  },
}, {
  async cancel({ commit }, loanId) {
    try {
      const ajax = Vue.axios.put(
        `/loans/${loanId}/cancel`,
      );

      commit('ajax', ajax);

      await ajax;

      commit('ajax', null);
    } catch (e) {
      commit('ajax', null);

      const { request, response } = e;
      commit('error', { request, response });

      throw e;
    }
  },
  async cancelAction({ commit }, action) {
    try {
      const ajax = Vue.axios.put(
        `/loans/${action.loan_id}/actions/${action.id}/cancel`,
        action,
      );

      commit('ajax', ajax);

      await ajax;

      commit('ajax', null);
    } catch (e) {
      commit('ajax', null);

      const { request, response } = e;
      commit('error', { request, response });

      throw e;
    }
  },
  async completeAction({ commit }, action) {
    try {
      const ajax = Vue.axios.put(
        `/loans/${action.loan_id}/actions/${action.id}/complete`,
        action,
      );

      commit('ajax', ajax);

      await ajax;

      commit('ajax', null);
    } catch (e) {
      commit('ajax', null);

      const { request, response } = e;
      commit('error', { request, response });

      throw e;
    }
  },
  async createAction({ commit }, action) {
    try {
      const ajax = Vue.axios.post(
        `/loans/${action.loan_id}/actions`,
        action,
      );

      commit('ajax', ajax);

      await ajax;

      commit('ajax', null);
    } catch (e) {
      commit('ajax', null);

      const { request, response } = e;
      commit('error', { request, response });

      throw e;
    }
  },
  async test({ commit }, params) {
    const { CancelToken } = Vue.axios;
    let cancel;

    try {
      const ajax = Vue.axios.get(`/loanables/${params.loanable_id}/test`, {
        params,
        cancelToken: new CancelToken((c) => {
          cancel = c;
        }),
      });
      ajax.cancel = cancel;
      ajax.context = 'test';

      commit('ajax', ajax);

      const { data } = await ajax;

      commit('mergeItem', {
        estimated_insurance: data.insurance,
        estimated_price: data.price,
        loanable: {
          ...data,
        },
      });

      commit('ajax', null);
    } catch (e) {
      if (!e.message || e.message !== 'loans canceled test') {
        throw e;
      }
    }
  },
});
