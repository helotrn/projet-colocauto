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
  exportFields: [
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
    'communities.id',
    'communities.name',
    'communities.proof.url',
    'communities.approved_at',
    'communities.suspended_at',
    'communities.tags.id',
    'communities.tags.name',
    'communities.tags.slug',
    'payment_methods.id',
    'payment_methods.type',
    'payment_methods.credit_card_type',
    'payment_methods.external_id',
  ],
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
  async updateEmail({ commit }, { userId, currentPassword, newEmail }) {
    try {
      const ajax = Vue.axios.post(`/users/${userId}/email`, {
        password: currentPassword,
        email: newEmail,
      });

      commit('ajax', ajax);

      const { data: item } = await ajax;

      commit('mergeItem', item);

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
  },
  async update({ commit, state, rootState }, { id, data, params }) {
    commit('loaded', false);
    try {
      const ajax = Vue.axios.put(`/${state.slug}/${id}`, data, {
        params: {
          ...params,
        },
      });

      commit('ajax', ajax);
      const { data: item } = await ajax;

      if (rootState.user.id === item.id) {
        commit('user', { ...item }, { root: true });
      }
      commit('item', item);
      commit('initialItem', item);

      commit('loaded', true);

      commit('ajax', null);
    } catch (e) {
      commit('ajax', null);

      const { request, response } = e;
      commit('error', { request, response });

      throw e;
    }
  },
});
