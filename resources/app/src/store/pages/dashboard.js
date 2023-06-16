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
  carsList: [],
  carsListed: false,
  balance: {},
  balanceLoaded: false,
  totalMembers: 0,
  totalLoanables: 0,
  loadRequests: 0,
};

const maxLoanableCount = 4;
const maxMemberCount = 4;

const actions = {
  async reload({ commit, dispatch }, user) {
    commit("reloading");
    dispatch("loadLoans");
    dispatch("loadLoanables");
    dispatch("listLoanableCars");
    dispatch("loadMembers", { user });
    if( user.communities && user.communities[0] ) {
      dispatch("loadBalance", { user });
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

      commit("membersLoaded", members);
    } catch (e) {
      commit("errorLoading", e);
      throw e;
    }
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
  async listLoanableCars({ commit }) {
    commit("loadLoanables");

    try {
      const { data } = await Vue.axios.get("/loanables/list", {
        params: {
          types: 'car',
        },
      });
      commit("carsListed", data.cars);
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
  carsListed(state, cars) {
    state.carsListed = true;
    state.carsList = cars;
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
