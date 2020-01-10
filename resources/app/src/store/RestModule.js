export default function RestModule(slug, initialState) {
  return {
    namespaced: true,
    state: {
      ...initialState,
      ajax: null,
      data: [],
      dataById: {},
      lastLoadedAt: null,
      loaded: false,
      loading: false,
      search: [],
      lastSearchQuery: '',
      slug,
      total: undefined,
    },
    mutations: {
      ajax(state, ajax) {
        state.ajax = ajax;
      },
      data(state, data) {
        state.data.splice(0, data.length, data);
      },
      addData(state, data) {
        state.data.push(...data);
      },
      loaded(state, loaded) {
        state.loaded = loaded;
      },
      loading(state, loading) {
        state.loading = loading;
      },
      setSearch(state, search) {
        state.search.splice(0, state.search, search);
      },
      setTotal(state, total) {
        state.total = total;
      }
    },
    actions: {
      load({ state, dispatch }) {
        // dispatch retrieve
        console.log('load');
      },
      search(state, search) {
        // dispatch retrieve with param "q"
        console.log('search');
      },
      create(state, data) {
        // call API POST
        console.log('create');
      },
      retrieve({ state, commit }, { params }) {
        // call API GET
        console.log('retrieve', params);
      },
      update(state, data) {
        // call API POST
        console.log('create');
      },
      delete(state, data) {
        // call API DELETE
        console.log('create');
      },
    },
  };
}
