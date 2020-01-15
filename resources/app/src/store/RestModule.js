import Vue from 'vue';

export default function RestModule(slug, initialState) {
  return {
    namespaced: true,
    state: {
      ajax: null,
      data: [],
      dataById: {},
      error: null,
      lastLoadedAt: null,
      loaded: false,
      loading: false,
      search: [],
      lastSearchQuery: '',
      params: {
        page: 1,
        per_page: 10,
        q: '',
        order: 'id',
      },
      slug,
      total: undefined,
      ...initialState,
    },
    mutations: {
      ajax(state, ajax) {
        state.ajax = ajax;
      },
      data(state, data) {
        state.data.splice(0, data.length, ...data);
      },
      addData(state, data) {
        state.data.push(...data);
      },
      error(state, error) {
        state.error = error;
      },
      lastLoadedAt(state, lastLoadedAt) {
        state.lastLoadedAt = lastLoadedAt;
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
      total(state, total) {
        state.total = total;
      },
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
      async retrieve({ state, commit }, params) {
        try {
          const ajax = Vue.axios.get(`/${state.slug}`, {
            ...state.params,
            ...params,
          });

          commit('ajax', ajax);

          const {
            data: {
              data,
              total,
            }
          } = await ajax;

          commit('data', data);
          commit('total', total);
          commit('lastLoadedAt', Date.now());

          commit('loaded', true);
          commit('loading', false);
          commit('ajax', null);
        } catch (e) {
          commit('loaded', false);
          commit('loading', false);
          commit('ajax', null);

          commit('error', e.response.data);

          throw e;
        }
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
