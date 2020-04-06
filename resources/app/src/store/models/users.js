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
  async approveBorrower({ commit }, userId) {
    try {
      const ajax = Vue.axios.put(`/users/${userId}/borrower/approve`);

      commit('ajax', ajax);

      const { data } = await ajax;

      commit('mergeItem', { borrower: data });

      commit('ajax', null);
    } catch (e) {
      commit('ajax', null);

      const { request, response } = e;
      commit('error', { request, response });

      throw e;
    }
  },
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
  async suspendBorrower({ commit }, userId) {
    try {
      const ajax = Vue.axios.put(`/users/${userId}/borrower/suspend`);

      commit('ajax', ajax);

      const { data } = await ajax;

      commit('mergeItem', { borrower: data });

      commit('ajax', null);
    } catch (e) {
      commit('ajax', null);

      const { request, response } = e;
      commit('error', { request, response });

      throw e;
    }
  },
  async unsuspendBorrower({ commit }, userId) {
    try {
      const ajax = Vue.axios.delete(`/users/${userId}/borrower/suspend`);

      commit('ajax', ajax);

      const { data } = await ajax;

      commit('mergeItem', { borrower: data });

      commit('ajax', null);
    } catch (e) {
      commit('ajax', null);

      const { request, response } = e;
      commit('error', { request, response });

      throw e;
    }
  },
  async updatePassword({ commit }, { userId, currentPassword, newPassword }) {
    try {
      const ajax = Vue.axios.post(`/users/${userId}/password`, {
        current: currentPassword,
        new: newPassword,
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
  }
});
