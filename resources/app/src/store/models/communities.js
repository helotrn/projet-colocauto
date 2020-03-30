import Vue from 'vue';
import RestModule from '../RestModule';

export default new RestModule('communities', {
  params: {
    order: 'name',
    page: 1,
    per_page: 10,
    q: '',
    type: null,
  },
}, {
  async setAmbassador({ commit }, { communityId, tagId, userId }) {
    try {
      const ajax = Vue.axios.put(`/communities/${communityId}`
        + `/users/${userId}/tags/${tagId}`);

      commit('ajax', ajax);

      const { data } = await ajax;

      commit('ajax', null);
    } catch (e) {
      commit('ajax', null);

      const { request, response } = e;
      commit('error', { request, response });

      throw e;
    }
  },
  async unsetAmbassador({ commit }, { communityId, tagId, userId }) {
    try {
      const ajax = Vue.axios.delete(`/communities/${communityId}`
        + `/users/${userId}/tags/${tagId}`);

      commit('ajax', ajax);

      const { data } = await ajax;

      commit('ajax', null);
    } catch (e) {
      commit('ajax', null);

      const { request, response } = e;
      commit('error', { request, response });

      throw e;
    }
  },
});
