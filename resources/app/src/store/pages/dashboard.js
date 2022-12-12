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
  members: [],
  membersLoaded: false,
  hasMoreMembers: false,
  totalMembers: 0,
  loadRequests: 0,
};

const maxLoanableCount = 5;
const maxMemberCount = 3;

const actions = {
  async reload({ commit, dispatch }, user) {
    commit("reloading");
    dispatch("loadLoans");
    dispatch("loadLoanables");
    dispatch("loadMembers", { user });
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
  async loadLoanables({ commit }) {
    commit("loadLoanables");

    try {
      const { data: loanables } = await Vue.axios.get("/loanables", {
        params: {
          order: "-updated_at",
          per_page: maxLoanableCount,
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
  async loadMembers({ commit }, { user }) {
    commit("loadMembers");

    try {
      const { data: members } = await Vue.axios.get("/users", {
        params: {
          order: "-created_at",
          per_page: maxMemberCount,
          fields: "id,full_name,tags,avatar,phone,communities.role,communities.proof,communities.approved_at,communities.suspended_at,owner",
        },
      });

      // exclude current user from the list
      members.data = members.data.filter(m => m.id !== user.id);
      members.total--;

      commit("membersLoaded", members);
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
  loadMembers(state) {
    state.loadRequests++;
  },
  membersLoaded(state, members) {
    state.membersLoaded = true;
    state.members = members.data;
    state.hasMoreMembers = members.total > maxMemberCount;
    state.totalMembers = members.total;
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
