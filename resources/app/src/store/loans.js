import Vue from 'vue';
import RestModule from './RestModule';

export default new RestModule('loans', {
  params: {
    order: '-created_at',
    page: 1,
    per_page: 10,
  },
}, {
  async completeAction({ commit, dispatch, state }, action) {
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
});
