import Vue from "vue";
import Vuex from "vuex";
import VuexPersist from "vuex-persist";
import merge from "deepmerge";

import account from "./account";
import global from "./global";
import passwordModule from "./password";
import stats from "./stats";

import bikes from "./models/bikes";
import borrowers from "./models/borrowers";
import cars from "./models/cars";
import communities from "./models/communities";
import files from "./models/files";
import images from "./models/images";
import invoices from "./models/invoices";
import loans from "./models/loans";
import loanables from "./models/loanables";
import padlocks from "./models/padlocks";
import paymentMethods from "./models/paymentMethods";
import tags from "./models/tags";
import trailers from "./models/trailers";
import owners from "./models/owners";
import users from "./models/users";
import invitations from "./models/invitations";
import expenses from "./models/expenses";
import expense_tags from "./models/expense_tags";
import refunds from "./models/refunds";

import AdminCommunity from "./pages/admin/community";
import CommunityMap from "./pages/community/map";
import LoanableSearch from "./pages/loanable/search";
import Dashboard from "./pages/dashboard";
import Login from "./pages/login";
import Register from "./pages/register";
import RegisterIntent from "./pages/register/intent";

Vue.use(Vuex);

/*
  Store is divided into modules:
    https://vuex.vuejs.org/guide/modules.html

  Root state is described in initialState below.
*/
const modules = {
  // General modules
  account,
  global,
  password: passwordModule,
  stats,

  // Rest modules: containing data for each entity.
  bikes,
  borrowers,
  cars,
  communities,
  files,
  images,
  invoices,
  loans,
  loanables,
  owners,
  padlocks,
  paymentMethods,
  tags,
  trailers,
  users,
  invitations,
  expenses,
  expense_tags,
  refunds,

  // Page modules
  "community.map": CommunityMap,
  "loanable.search": LoanableSearch,
  dashboard: Dashboard,
  login: Login,
  register: Register,
  "register.intent": RegisterIntent,
  "admin.community": AdminCommunity,
};

const initialState = {
  // Initial root state.
  // Initial state of modules is set in each module individually.
  loaded: false,
  loading: false,
  loansLoaded: false,
  loanablesLoaded: false,
  hasMoreLoanables: false,
  notifications: [],
  user: null,
  token: null,
  refreshToken: null,
  seenVersions: [],
};

const loadUserFields = [
  "*",
  "avatar.*",
  "borrower.*",
  "communities.*",
  "owner.*",
  "payment_methods.*",
].join(",");

const actions = {
  async loadUser({ commit, state }) {
    commit("loading", true);

    try {
      const { data: user } = await Vue.axios.get("/auth/user", {
        params: {
          fields: loadUserFields,
        },
      });

      const newUser = {
        ...user,
        loanables: state.user?.loanables ?? [],
        loans: state.user?.loans ?? [],
      };

      commit("user", newUser);
      commit("account/transactionId", user.transaction_id + 1);
    } catch (e) {
      commit("loading", false);
      throw e;
    }

    commit("loaded", true);
    commit("loading", false);
  },
  async login({ commit, dispatch, state }, { email, password }) {
    try {
      commit("loading", true);

      const { data } = await Vue.axios.post("/auth/login", {
        email,
        password,
        rememberMe: state.login.rememberMe,
      });

      commit("token", data.access_token);
      commit("refreshToken", data.refresh_token);

      if (!state.login.rememberMe) {
        commit("login/email", "");
      }
    } catch (e) {
      commit("loading", false);
      throw e;
    }

    await dispatch("loadUser");
    await dispatch("global/load");
  },
  async register({ commit, dispatch, state }, { email, password, invitationToken }) {
    try {
      commit("loading", true);
      const { data } = await Vue.axios.post("/auth/register", {
        email,
        password,
        invitationToken,
      });

      commit("token", data.access_token);
      commit("refreshToken", data.refresh_token);
    } catch (e) {
      commit("loading", false);
      throw e;
    }

    if (!state.login.rememberMe) {
      commit("login/email", "");
    }

    await dispatch("loadUser");
  },
  async submitUser({ commit }) {
    commit("loading", true);

    try {
      const { data: user } = await Vue.axios.put(
        "/auth/user/submit",
        {},
        {
          params: {
            fields: loadUserFields,
          },
        }
      );

      commit("user", user);

      commit("loaded", true);
      commit("loading", false);
    } catch (e) {
      throw e;
    }
  },
  logout({ commit }) {
    commit("token", null);
    commit("refreshToken", null);
    commit("user", null);

    commit("loaded", true);
    commit("loading", false);
    commit("reset");
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
  seeVersion(state, version) {
    state.seenVersions.push(version);
  },
  unseeVersion(state, version) {
    const index = state.seenVersions.indexOf(version);
    if (index > -1) {
      state.seenVersions.splice(index, 1);
    }
  },
  token(state, token) {
    state.token = token;
  },
  user(state, user) {
    state.user = user;
  },
  reset(state) {
    Object.assign(state, initialState);
  },
};

const vuexPersist = new VuexPersist({
  key: "locomotion",
  storage: window.localStorage,
  /*
     State reducer. Reduces state to only those values you want to save.
     By default, saves entire state.
     https://github.com/championswimmer/vuex-persist#constructor-parameters--
   */
  reducer: (state) => ({
    // Root state
    user: state.user,
    token: state.token,
    refreshToken: state.refreshToken,
    seenVersions: state.seenVersions,

    // General modules
    stats: state.stats,

    // Rest modules
    loans: {
      item: state.loans.item,
    },

    // Page modules
    "loanable.search": {
      ...state["loanable.search"],
      center: null,
    },
    login: {
      email: state.login.email,
      rememberMe: state.login.rememberMe,
    },
    "register.intent": state["register.intent"],
  }),
});

export default new Vuex.Store({
  modules,
  state: initialState,
  mutations,
  actions,
  plugins: [vuexPersist.plugin],
});
