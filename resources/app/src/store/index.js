import Vue from 'vue';
import Vuex from 'vuex';
import VuexPersist from 'vuex-persist';
import merge from 'deepmerge';

import communities from './communities';
import files from './files';
import images from './images';
import loanables from './loanables';
import login from './pages/login';
import register from './pages/register';
import users from './users';

import RegisterMap from './pages/register/map';

const vuexPersist = new VuexPersist({
  key: 'locomotion',
  storage: window.localStorage,
  reducer: state => ({
    token: state.token,
    refreshToken: state.refreshToken,
    user: state.user,
    login: {
      email: state.login.email,
      rememberMe: state.login.rememberMe,
    },
  }),
});

Vue.use(Vuex);

const initialState = {
  loaded: false,
  loading: false,
  notifications: [],
  user: null,
  token: null,
  refreshToken: null,
};

const actions = {
  async loadUser({ commit, dispatch }) {
    commit('loading', true);

    try {
      const { data: user } = await Vue.axios.get('/auth/user', {
        params: {
          fields: '*,avatar.*,communities.id,communities.name,communities.role',
        },
      });

      commit('user', user);

      commit('loaded', true);
      commit('loading', false);
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

    if (!state.login.rememberMe) {
      commit('login/email', '');
    }

    await dispatch('loadUser');
  },
  async register({ commit, dispatch, state }, { email, password }) {
    const { data } = await Vue.axios.post('/auth/register', {
      email,
      password,
    });

    commit('token', data.access_token);
    commit('refreshToken', data.refresh_token);

    if (!state.login.rememberMe) {
      commit('login/email', '');
    }

    await dispatch('loadUser');
  },
  logout({ commit }) {
    commit('token', null);
    commit('refreshToken', null);
    commit('user', null);

    commit('loaded', true);
    commit('loading', false);
  },
};

const mutations = {
  addNotification(state, notification) {
    state.notifications.push(notification);
  },
  removeNotification(state, notification) {
    const index = state.notifications.indexOf(notification);

    if (index > -1) {
      state.notifications.splice(index, 1);
    }
  },
  loaded(state, value) {
    state.loaded = value;
  },
  loading(state, value) {
    state.loading = value;
  },
  mergeUser(state, user) {
    state.user = merge(state.user, user);
  },
  refreshToken(state, refreshToken) {
    state.refreshToken = refreshToken;
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
    files,
    images,
    loanables,
    login,
    register,
    'register.map': RegisterMap,
    users,
  },
  plugins: [vuexPersist.plugin],
});
