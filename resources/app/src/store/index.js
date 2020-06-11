import Vue from 'vue';
import Vuex from 'vuex';
import VuexPersist from 'vuex-persist';
import merge from 'deepmerge';

import account from './account';
import global from './global';
import passwordModule from './password';
import stats from './stats';

import bikes from './models/bikes';
import borrowers from './models/borrowers';
import cars from './models/cars';
import communities from './models/communities';
import files from './models/files';
import images from './models/images';
import invoices from './models/invoices';
import loans from './models/loans';
import loanables from './models/loanables';
import padlocks from './models/padlocks';
import paymentMethods from './models/paymentMethods';
import tags from './models/tags';
import trailers from './models/trailers';
import owners from './models/owners';
import users from './models/users';

import CommunityView from './pages/community/view';
import Login from './pages/login';
import Register from './pages/register';
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
    'community.view': state['community.view'],
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
  'avatar.*',
  'borrower.*',
  'communities.*',
  'loanables.*',
  '!loanables.events',
  'loanables.image.*',
  'loanables.loans.*',
  'loanables.loans.actions.*',
  'loanables.loans.borrower.id',
  'loanables.loans.borrower.user.avatar.*',
  'loanables.loans.borrower.user.full_name',
  'loanables.loans.borrower.user.id',
  'loans.*',
  'loans.total_final_cost',
  '!loans.actual_price',
  '!loans.actual_insurance',
  'loans.actions.*',
  'loans.borrower.id',
  'loans.borrower.user.avatar',
  'loans.borrower.user.full_name',
  'loans.borrower.user.id',
  'loans.loanable.id',
  'loans.loanable.community.name',
  'loans.loanable.image.*',
  'loans.loanable.name',
  'loans.loanable.owner.id',
  'loans.loanable.owner.user.avatar.*',
  'loans.loanable.owner.user.full_name',
  'loans.loanable.owner.user.id',
  'loans.loanable.type',
  'owner.*',
  'payment_methods.*',
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

    commit('account/transactionId', user.transaction_id + 1);

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
    await dispatch('global/load');
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
    account,
    bikes,
    borrowers,
    cars,
    communities,
    'community.view': CommunityView,
    files,
    global,
    images,
    invoices,
    loans,
    loanables,
    login: Login,
    owners,
    padlocks,
    password: passwordModule,
    paymentMethods,
    'profile.loanable': ProfileLoanable,
    register: Register,
    'register.intent': RegisterIntent,
    'register.map': RegisterMap,
    stats,
    tags,
    trailers,
    users,
  },
  plugins: [vuexPersist.plugin],
});
