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
  exportFields: [
    'id',
    'name',
    'type',
    'center',
    'area',
    'area_google',
  ],
}, {
  async addUser({ commit }, { id, data }) {
    try {
      const ajax = Vue.axios.post(`/communities/${id}/users`, data, {
        params: {
          fields: '*,communities.*',
        },
      });

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
  async removeUser({ commit }, { id, userId }) {
    try {
      const ajax = Vue.axios.delete(`/communities/${id}/users/${userId}`);

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
  async updateUser({ commit }, { id, userId, data }) {
    try {
      const ajax = Vue.axios.put(`/communities/${id}/users/${userId}`, data, {
        params: {
          fields: '*,communities.*',
        },
      });

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
  async setCommittee({ commit }, { communityId, tagId, userId }) {
    try {
      const ajax = Vue.axios.put(`/communities/${communityId}`
        + `/users/${userId}/tags/${tagId}`);

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
  async unsetCommittee({ commit }, { communityId, tagId, userId }) {
    try {
      const ajax = Vue.axios.delete(`/communities/${communityId}`
        + `/users/${userId}/tags/${tagId}`);

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
