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

import AdminCommunity from "./pages/admin/community";
import CommunityMap from "./pages/community/map";
import CommunityView from "./pages/community/view";
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

  // Page modules
  "community.map": CommunityMap,
  "community.view": CommunityView,
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
  async loadUserAndLoans({ dispatch }) {
    await dispatch("loadUser");
    dispatch("loadAllLoans");
  },
  async loadAllLoans({ dispatch }) {
    dispatch("loadLoans").then(() => dispatch("loadLoanables"));
  },
  async loadUser({ commit, state }) {
    commit("loading", true);

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

    commit("loaded", true);
    commit("loading", false);
  },

  async loadLoans({ commit, state }) {
    if (!state.user.borrower || !state.user.borrower.id) {
      const newUser = {
        ...state.user,
        loans: [],
      };

      commit("user", newUser);
      commit("loansLoaded", true);
      return;
    }
    commit("loansLoaded", false);
    commit("loading", true);

    const { data: loans } = await Vue.axios.get("/loans", {
      params: {
        order: "-updated_at",
        per_page: 30,
        "borrower.id": state.user.borrower.id,
        fields: [
          "*",
          "actions.*",
          "loanable.id",
          "loanable.community.name",
          "loanable.image.*",
          "loanable.name",
          "loanable.owner.id",
          "loanable.owner.user.avatar.*",
          "loanable.owner.user.full_name",
          "loanable.owner.user.id",
          "loanable.type",
        ].join(","),
        status: "in_process",
      },
    });

    const newUser = {
      ...state.user,
      loans: loans.data,
    };

    commit("user", newUser);
    commit("loansLoaded", true);
    commit("loading", false);
  },
  async loadLoanables({ commit, state }) {
    if (!state.user.owner || !state.user.owner.id) {
      const newUser = {
        ...state.user,
        loanables: [],
      };

      commit("loanablesLoaded", true);
      commit("user", newUser);
      return;
    }

    commit("loanablesLoaded", false);
    commit("loading", true);

    const maxLoanableCount = 5;

    const { data: loanables } = await Vue.axios.get("/loanables", {
      params: {
        order: "-updated_at",
        per_page: maxLoanableCount,
        "owner.id": state.user.owner.id,
        fields: [
          "*",
          "!events",
          "image.*",
          "loans.*",
          "loans.actions.*",
          "loans.borrower.id",
          "loans.borrower.user.avatar.*",
          "loans.borrower.user.full_name",
          "loans.borrower.user.id",
        ].join(","),
      },
    });

    const newUser = {
      ...state.user,
      loanables: loanables.data,
      hasMoreLoanables: loanables.total > maxLoanableCount,
    };

    commit("user", newUser);
    commit("loanablesLoaded", true);
    commit("loading", false);
  },
  async login({ commit, dispatch, state }, { email, password }) {
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

    await dispatch("loadUser");
    await dispatch("global/load");
    dispatch("loadAllLoans");
  },
  async register({ commit, dispatch, state }, { email, password }) {
    const { data } = await Vue.axios.post("/auth/register", {
      email,
      password,
    });

    commit("token", data.access_token);
    commit("refreshToken", data.refresh_token);

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
  loansLoaded(state, value) {
    state.loansLoaded = value;
  },
  loanablesLoaded(state, value) {
    state.loanablesLoaded = value;
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
    "community.view": {
      ...state["community.view"],
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
