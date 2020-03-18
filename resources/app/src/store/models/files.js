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
  },
  actions: {
    async upload({ commit }, formData) {
      const ajax = Vue.axios.post('/files', formData);

      commit('ajax', ajax);

      const { data: image } = await ajax;

      commit('ajax', null);

      return image;
    },
  },
};
