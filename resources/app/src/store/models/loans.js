import Vue from 'vue';
import RestModule from '../RestModule';

export default new RestModule('loans', {
  params: {
    order: '-created_at',
    page: 1,
    per_page: 10,
  },
}, {
  async cancelAction({ commit, state }, action) {
    try {
      const ajax = Vue.axios.put(
        `/${state.slug}/${action.loan_id}/actions/${action.id}/cancel`,
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
  async completeAction({ commit, state }, action) {
    try {
      const ajax = Vue.axios.put(
        `/${state.slug}/${action.loan_id}/actions/${action.id}/complete`,
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

    const ajax = Vue.axios.get(`/loanables/${params.loanable_id}/test`, {
      params,
      cancelToken: new CancelToken((c) => {
        cancel = c;
      }),
    });
    ajax.cancel = cancel;

    commit('ajax', ajax);

    const { data } = await ajax;

    commit('mergeItem', {
      estimated_price: data.price,
      loanable: {
        ...data,
      },
    });

    commit('ajax', null);
  },
});
