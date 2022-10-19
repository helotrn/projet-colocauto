import Vue from "vue";

const initialState = {
  loans: {
    future: [],
    started: [],
    contested: [],
    waiting: [],
    completed: [],
    need_approval: [],
  },
  loanables: [],
  loaded: false,
  loansLoaded: false,
  loanablesLoaded: false,
  hasMoreLoanables: false,
  loadRequests: 0,
};

const maxLoanableCount = 5;

const actions = {
  async reload({ commit, dispatch }, owner) {
    commit("reloading");
    dispatch("loadLoans");
    if (owner) {
      dispatch("loadLoanables", owner.id);
    }
  },
  async loadLoans({ commit }) {
    commit("loadLoans");

    try {
      const { data: loans } = await Vue.axios.get("/loans/dashboard");
      commit("loansLoaded", loans);
    } catch (e) {
      commit("errorLoading", e);
      throw e;
    }
  },
  async loadLoanables({ commit }, owner_id) {
    commit("loadLoanables");

    try {
      const { data: loanables } = await Vue.axios.get("/loanables", {
        params: {
          order: "-updated_at",
          per_page: maxLoanableCount,
          "owner.id": owner_id,
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
      commit("loanablesLoaded", loanables);
    } catch (e) {
      commit("errorLoading", e);
      throw e;
    }
  },
};

const mutations = {
  reloading(state) {
    state.loansLoaded = false;
    state.loanablesLoaded = false;
  },
  loadLoans(state) {
    state.loadRequests++;
  },
  loadLoanables(state) {
    state.loadRequests++;
  },
  loansLoaded(state, loans) {
    state.loans = loans;
    state.loansLoaded = true;
    state.loadRequests--;
  },
  loanablesLoaded(state, loanables) {
    state.loanablesLoaded = true;
    state.loanables = loanables.data;
    state.hasMoreLoanables = loanables.total > maxLoanableCount;
    state.loadRequests--;
  },
  errorLoading(state) {
    state.loadRequests--;
  },
  setLoanables(state, loanables) {
    state.loanables = loanables;
  },
};

export default {
  namespaced: true,
  state: initialState,
  actions,
  mutations,
};
