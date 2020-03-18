import Vue from 'vue';
import RestModule from '../RestModule';

export default new RestModule('users', {
  params: {
    order: 'name',
    page: 1,
    per_page: 10,
    q: '',
    type: null,
  },
}, {
  async joinCommunity({ commit }, { communityId, userId }) {
    try {
      const ajax = Vue.axios.put(`/users/${userId}/communities/${communityId}`);

      commit('ajax', ajax);

      const { data } = await ajax;

      commit('mergeItem', { communities: [data] });

      commit('ajax', null);
    } catch (e) {
      commit('ajax', null);

      const { request, response } = e;
      commit('error', { request, response });

      throw e;
    }
  },
});
