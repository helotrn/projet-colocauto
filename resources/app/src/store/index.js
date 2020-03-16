import Vue from 'vue';
import Vuex from 'vuex';
import VuexPersist from 'vuex-persist';
import merge from 'deepmerge';

import stats from './stats';
import bikes from './bikes';
import cars from './cars';
import communities from './communities';
import files from './files';
import images from './images';
import loans from './loans';
import loanables from './loanables';
import login from './pages/login';
import register from './pages/register';
import trailers from './trailers';
import users from './users';

import CommunityList from './pages/community/list';

import RegisterIntent from './pages/register/intent';
import RegisterMap from './pages/register/map';

import ProfileLoanable from './pages/profile/loanable';

const vuexPersist = new VuexPersist({
  key: 'locomotion',
  storage: window.localStorage,
  reducer: state => ({
    token: state.token,
    refreshToken: state.refreshToken,
    user: state.user,
    loans: {
      item: state.loans.item,
    },
    login: {
      email: state.login.email,
      rememberMe: state.login.rememberMe,
    },
    stats: state.stats,
    'community.list': state['community.list'],
    'register.intent': state['register.intent'],
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

const loadUserFields = [
  '*',
  'loanables.*',
  '!loanables.events',
  'loans.*',
  'loans.borrower.id',
  'loans.borrower.user.id',
  'loans.borrower.user.full_name',
  'loans.borrower.user.avatar',
  'loans.loanable.id',
  'loans.loanable.type',
  'loans.loanable.name',
  'loans.loanable.owner.id',
  'loans.loanable.owner.user.id',
  'loans.loanable.owner.user.full_name',
  'loans.loanable.owner.user.avatar.*',
  'loans.actions.*',
  'loanables.loans.*',
  'loanables.loans.borrower.id',
  'loanables.loans.borrower.user.id',
  'loanables.loans.borrower.user.full_name',
  'loanables.loans.borrower.user.avatar.*',
  'loanables.loans.actions.*',
  'avatar.*',
  'owner.*',
  'borrower.*',
  'communities.*',
].join(',');

const actions = {
  async loadUser({ commit }) {
    commit('loading', true);

    const { data: user } = await Vue.axios.get('/auth/user', {
      params: {
        fields: loadUserFields,
      },
    });

    commit('user', user);

    commit('loaded', true);
    commit('loading', false);
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
  async submitUser({ commit }) {
    commit('loading', true);

    try {
      const { data: user } = await Vue.axios.put('/auth/user/submit', {}, {
        params: {
          fields: loadUserFields,
        },
      });

      commit('user', user);

      commit('loaded', true);
      commit('loading', false);
    } catch (e) {
      throw e;
    }
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
    bikes,
    cars,
    communities,
    'community.list': CommunityList,
    files,
    images,
    loans,
    loanables,
    login,
    'profile.loanable': ProfileLoanable,
    register,
    'register.intent': RegisterIntent,
    'register.map': RegisterMap,
    stats,
    trailers,
    users,
  },
  plugins: [vuexPersist.plugin],
});
