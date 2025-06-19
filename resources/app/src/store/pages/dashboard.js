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
  balance: {},
  balanceLoaded: false,
  totalMembers: 0,
  totalLoanables: 0,
  loadRequests: 0,
};

const maxLoanableCount = 8;
const maxMemberCount = 8;

const actions = {
  async reload({ commit, dispatch, rootState }, user) {
    commit("reloading");
    dispatch("loadLoans");
    dispatch("loadLoanables");
    dispatch("loadMembers", { user });
    if( user.communities && user.communities[0] ) {
      dispatch("loadBalance", { community: user.communities[0] });
    }
  },
  async loadLoans({ commit }) {
    commit("loadLoans");

    try {
      const { data: loans } = await Vue.axios.get("/loans/dashboard", {params: {borrower: 'me'}});
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
            "name", "owner", "estimated_cost",
            "!events",
            "image.*",
            "community.id",
            "community.name",
          ].join(","),
        },
      });
      commit("loanablesLoaded", loanables);
    } catch (e) {
      commit("errorLoading", e);
      throw e;
    }
  },
  async loadMembers({ commit, rootState }, { user }) {
    commit("loadMembers");

    try {
      const { data: members } = await Vue.axios.get("/users", {
        params: {
          order: "-created_at",
          "communities.id": rootState.communities.current ?? user.main_community?.id,
          per_page: maxMemberCount,
          fields: "id,full_name,tags,avatar,phone,owner",
        },
      });

      commit("membersLoaded", members);
    } catch (e) {
      commit("errorLoading", e);
      throw e;
    }
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
};

const mutations = {
  reloading(state) {
    state.loansLoaded = false;
    state.loanablesLoaded = false;
    state.balanceLoaded = false;
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
    state.totalLoanables = loanables.total;
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
  balanceLoaded(state, balance) {
    state.balanceLoaded = true;
    state.balance = balance;
    state.loadRequests--;
  },
  loadBalance(state) {
    state.loadRequests++;
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
