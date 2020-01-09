import Vue from 'vue';
import Vuex from 'vuex';
import VuexPersist from 'vuex-persist';

import communities from './communities';
import login from './pages/login';

const vuexPersist = new VuexPersist({
  key: 'locomotion',
  storage: window.localStorage,
  reducer: state => ({
    token: state.token,
    refreshToken: state.refreshToken,
    user: state.user,
  }),
});

Vue.use(Vuex);

const initialState = {
  initialized: false,
  notifications: [],
  user: null,
  token: null,
  refreshToken: null,
};

const actions = {
  async loadUser({ commit, dispatch, state }) {
    if (state.token) {
      Vue.axios.defaults.headers.common.Authorization = `Bearer ${state.token}`;
    } else {
      delete Vue.axios.defaults.headers.common;
    }

    try {
      const { data: user } = await Vue.axios.get('/auth/user');

      commit('user', user);
    } catch (e) {
      dispatch('logout');
    }
  },
  async login({ commit, dispatch, state }, { email, password }) {
    const { data } = await Vue.axios.post('/auth/login', {
      email,
      password,
      rememberMe: state.login.rememberMe,
    });

    commit('token', data.access_token);
    commit('refreshToken', data.refresh_token);

    await dispatch('loadUser');
  },
  logout({ commit }) {
    commit('initialized', true);
    commit('token', null);
    commit('user', null);
  },
};

const mutations = {
  addNotification(state, notification) {
    state.notifications.push(notification);
  },
  removeNotification(state, notification) {
    const index = state.notifications.indexOf(
      state.notifications.find(n => n.id === notification.id)
    );

    if (index > -1) {
      state.notifications.splice(index, 1);
    }
  },
  initialized(state, value) {
    state.initialized = value;
  },
  refreshToken(state, refreshToken) {
    state.refreshToken = refreshToken;
  },
  token(state, token) {
    state.token = token;
    if (token) {
      Vue.axios.defaults.headers.common.Authorization = `Bearer ${token}`;
    } else {
      delete Vue.axios.defaults.headers.common;
    }
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
