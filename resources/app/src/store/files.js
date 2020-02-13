import Vue from 'vue';

export default {
  namespaced: true,
  state: {
    ajax: false,
    errors: {},
  },
  mutations: {
    ajax(state, ajax) {
      state.ajax = ajax;
    },
    loading(state, loading) {
      state.loading = loading;
    },
  },
  actions: {
    async upload({ commit }, formData) {
      commit('loading', true);

      const ajax = Vue.axios.post('/files', formData);

      commit('ajax', ajax);

      const { data: image } = await ajax;

      commit('ajax', null);

      return image;
    },
  },
};
