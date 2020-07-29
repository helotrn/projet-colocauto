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
  usersExportFields: [
    'id',
    'created_at',
    'submitted_at',
    'address',
    'date_of_birth',
    'description',
    'email',
    'is_smart_phone',
    'last_name',
    'name',
    'other_phone',
    'password',
    'phone',
    'postal_code',
    'opt_in_newsletter',
    'borrower.drivers_licence_number',
    'borrower.approved_at',
    'proof.url',
    'approved_at',
    'suspended_at',
    'tags.id',
    'tags.name',
    'tags.slug',
  ],
  usersExportUrl: null,
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
  async exportUsers({ state, commit }, params) {
    try {
      const ajax = Vue.axios.get(`/${state.slug}/${state.item.id}/users`, {
        params: {
          ...state.params,
          ...params,
          per_page: 1000000,
          page: 1,
          fields: state.usersExportFields.join(','),
        },
        headers: {
          Accept: 'text/csv',
        },
      });

      commit('ajax', ajax);

      const { data: url } = await ajax;

      commit('usersExportUrl', url);

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
}, {
  usersExportUrl(state, usersExportUrl) {
    state.usersExportUrl = usersExportUrl;
  },
});
