import Vue from "vue";

const initialState = {
  balance: {},
  balanceLoaded: false,
  loading: true,
}

const actions = {
  async reload({ commit, dispatch, rootState }, user) {
    commit("reloading");
    dispatch("loadBalance", { community: user.communities[0] });
  },

  async loadBalance({ commit }, { community }) {
    commit("loadBalance");

    try {
      const { data: balance } = await Vue.axios.get(`/communities/${community.id}/balance`);
      commit("balanceLoaded", balance);
    } catch (e) {
      commit("errorLoading", e);
      throw e;
    }
  },

  async filterExpenses({ commit, dispatch }, params) {
    try {
      commit("expenses/setFilterParams", params, {root: true});
    } catch (e) {
      commit("errorLoading", e);
      throw e;
    }
  },

  async filterRefunds({ commit, dispatch }, params) {
    try {
      commit("refunds/setFilterParams", params, {root: true});
    } catch (e) {
      commit("errorLoading", e);
      throw e;
    }
  },
};

const mutations = {
  reloading(state) {
    state.balanceLoaded = false;
  },
  balanceLoaded(state, balance) {
    state.balanceLoaded = true;
    state.balance = balance;
    state.loading = false;
  },
  loadBalance(state) {
    state.loading = true;
  },
  errorLoading(state) {
    state.loading = false;
  },
};

export default {
  namespaced: true,
  state: initialState,
  actions,
  mutations,
};
