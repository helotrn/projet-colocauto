import Vue from 'vue';
import RestModule from './RestModule';

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
      const ajax = Vue.axios.post(`/users/${userId}/communities/${communityId}`);

      commit('ajax', ajax);

      const { data } = await ajax;

      commit('ajax', null);

      commit('mergeUser', data, { root: true });
      commit('mergeItem', data);
    } catch (e) {
      commit('ajax', null);

      commit('error', e.response.data);

      throw e;
    }
  },
});
