import Vue from 'vue';

export default function RestModule(slug, initialState) {
  return {
    namespaced: true,
    state: {
      ajax: null,
      data: [],
      dataById: {},
      error: null,
      initialItem: null,
      item: null,
      lastLoadedAt: null,
      loaded: false,
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
      item(state, item) {
        state.item = item;
      },
      initialItem(state, item) {
        state.initialItem = JSON.stringify(item);
      },
      lastLoadedAt(state, lastLoadedAt) {
        state.lastLoadedAt = lastLoadedAt;
      },
      loaded(state, loaded) {
        state.loaded = loaded;
      },
      setParam(state, { name, value }) {
        Vue.set(state.params, name, value);
      },
      setSearch(state, search) {
        state.search.splice(0, state.search, search);
      },
      setOrder(state, { field, direction }) {
        switch (direction) {
          case 'desc':
            state.params.order = `-${field}`;
            break;
          case 'asc':
          default:
            state.params.order = `${field}`;
            break;
        }
      },
      total(state, total) {
        state.total = total;
      },
    },
    actions: {
      async load({ dispatch, state }) {
        await dispatch('retrieve', state.params);
      },
      async loadEmpty({ commit, state }) {
        try {
          const ajax = Vue.axios.options(`/${state.slug}`)

          commit('ajax', ajax);

          const { data } = await ajax;

          commit('item', data);
          commit('initialItem', data);

          commit('ajax', null);
        } catch (e) {
          commit('loaded', false);

          commit('ajax', null);

          commit('error', e.response.data);

          throw e;
        }
      },
      async loadOne({ dispatch }, id) {
        await dispatch('retrieveOne', { id });
      },
      search() { // state, search) {
        // dispatch retrieve with param "q"
      },
      create() { // state, data) {
        // call API POST
      },
      async retrieveOne({ state, commit }, { params, id }) {
        try {
          const ajax = Vue.axios.get(`/${state.slug}/${id}`, {
            params: {
              ...params,
            },
          });

          commit('ajax', ajax);

          const { data } = await ajax;

          commit('item', data);
          commit('initialItem', data);

          commit('ajax', null);
        } catch (e) {
          commit('loaded', false);

          commit('ajax', null);

          commit('error', e.response.data);

          throw e;
        }
      },
      async retrieve({ state, commit }, params) {
        try {
          const ajax = Vue.axios.get(`/${state.slug}`, {
            params: {
              ...state.params,
              ...params,
            },
          });

          commit('ajax', ajax);

          const {
            data: {
              data,
              total,
            },
          } = await ajax;

          commit('data', data);
          commit('total', total);
          commit('lastLoadedAt', Date.now());

          commit('loaded', true);
          commit('ajax', null);
        } catch (e) {
          commit('loaded', false);
          commit('ajax', null);

          commit('error', e.response.data);

          throw e;
        }
      },
      async updateItem({ dispatch, state }) {
        await dispatch('update', { id: state.item.id, data: state.item });
      },
      async update({ commit, state }, { id, data }) {
        try {
          const ajax = Vue.axios.put(`/${state.slug}/${id}`, data);

          commit('ajax', ajax);

          await ajax;

          commit('loaded', false);
          commit('total', undefined);
          commit('ajax', null);
        } catch (e) {
          commit('loaded', false);
          commit('ajax', null);

          commit('error', e.response.data);

          throw e;
        }
      },
      delete() { // state, data) {
        // call API DELETE
      },
    },
  };
}
