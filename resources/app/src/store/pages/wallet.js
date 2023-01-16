import Vue from "vue";

const initialState = {
  balance: [],
  balanceLoaded: false,
  loading: true,
}

const actions = {
  async reload({ commit, dispatch }, user) {
    commit("reloading");
    dispatch("loadBalance", { user });
  },

  async loadBalance({ commit }, { user }) {
    commit("loadBalance");

    try {
      const { data: balance } = await Vue.axios.get(`/communities/${user.communities[0].id}/balance`);
      commit("balanceLoaded", balance);
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
