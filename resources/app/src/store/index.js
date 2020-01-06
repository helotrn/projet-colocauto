import Vue from 'vue';
import Vuex from 'vuex';
import VuexPersist from 'vuex-persist';

import communities from './communities';
import login from './pages/login';

const vuexPersist = new VuexPersist({
  key: 'locomotion',
  storage: window.localStorage,
});

Vue.use(Vuex);

const initialState = {
  initialized: false,
  error: null,
  user: null,
  token: null,
};

const actions = {
  async login({ state, commit }, { email, password }) {

  },
  logout({ commit }) {
    commit('initialized', true);
    commit('token', null);
    commit('user', null);
  },
};

const mutations = {
  initialized(state, value) {
    state.initialized = value;
  },
  token(state, token) {
    state.token = token;
  },
  user(state, user) {
    state.user = user;
  },
};

export default new Vuex.Store({
  state: initialState,
  mutations,
  actions,
  modules: {
    communities,
    login,
  },
  plugins: [vuexPersist.plugin],
});
