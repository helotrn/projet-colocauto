import Vue from 'vue';
import merge from 'deepmerge';

export default function RestModule(slug, initialState, actions = {}, mutations = {}) {
  return {
    namespaced: true,
    state: {
      ajax: null,
      data: [],
      dataById: {},
      deleted: null,
      empty: null,
      error: null,
      filters: {},
      form: null,
      initialItem: '',
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
      addData(state, data) {
        state.data.push(...data);
      },
      ajax(state, ajax) {
        state.ajax = ajax;
      },
      data(state, data) {
        state.data = data;
      },
      deleted(state, deleted) {
        state.deleted = deleted;
      },
      empty(state, empty) {
        state.empty = empty;
      },
      error(state, error) {
        state.error = error;
      },
      filters(state, filters) {
        state.filters = filters;
      },
      form(state, form) {
        state.form = form;
      },
      // SETTER : Sets the item as a whole
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
      // SETTER : Deep merges the partial in the item
      mergeItem(state, partial) {
        state.item = merge(state.item, partial);
      },
      // SETTER : Shallow merges the partial in the item
      patchItem(state, partial) {
        state.item = {
          ...state.item,
          ...partial,
        };
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
      ...mutations,
    },
    actions: {
      async load({ dispatch, state }) {
        await dispatch('retrieve', state.params);
      },
      async loadEmpty({ commit, dispatch, state }) {
        commit('loaded', false);

        try {
          await dispatch('options');

          commit('item', { ...state.empty });
          commit('initialItem', state.item);

          commit('loaded', true);

          commit('ajax', null);
        } catch (e) {
          commit('ajax', null);

          const { request, response } = e;
          commit('error', { request, response });

          throw e;
        }
      },
      search() { // state, search) {
        // dispatch retrieve with param "q"
      },
      async createItem({ dispatch, state }, params) {
        await dispatch('create', { data: state.item, params });
      },
      async create({ commit, dispatch, state }, { data, params }) {
        commit('loaded', false);

        try {
          const ajax = Vue.axios.post(`/${state.slug}`, data, {
            params: {
              ...params,
            },
          });

          commit('ajax', ajax);

          const { data: item } = await ajax;

          commit('item', item);
          commit('initialItem', item);

          commit('ajax', null);

          await dispatch('retrieve', state.params);
        } catch (e) {
          commit('ajax', null);

          const { request, response } = e;
          commit('error', { request, response });

          throw e;
        }
      },
      async options({ state, commit }) {
        if (state.form === null || state.filters === null || state.empty === null) {
          const options = Vue.axios.options(`/${state.slug}`);

          const {
            data: {
              item: empty,
              filters,
              form,
            },
          } = await options;

          commit('empty', empty);
          commit('filters', filters);
          commit('form', form);
        }
      },
      async retrieveOne({ dispatch, commit, state }, { params, id }) {
        commit('loaded', false);

        try {
          await dispatch('options');

          const ajax = Vue.axios.get(`/${state.slug}/${id}`, {
            params: {
              ...params,
            },
          });

          commit('ajax', ajax);

          const { data } = await ajax;

          commit('item', data);
          commit('initialItem', data);

          commit('loaded', true);

          commit('ajax', null);
        } catch (e) {
          commit('ajax', null);

          const { request, response } = e;
          commit('error', { request, response });

          throw e;
        }
      },
      async retrieve({ dispatch, state, commit }, params) {
        commit('loaded', false);

        try {
          await dispatch('options');

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
          commit('ajax', null);

          const { request, response } = e;
          commit('error', { request, response });

          throw e;
        }
      },
      async updateItem({ dispatch, state }, params) {
        await dispatch('update', { id: state.item.id, data: state.item, params });
      },
      async update({ commit, state }, { id, data, params }) {
        commit('loaded', false);

        try {
          const ajax = Vue.axios.put(`/${state.slug}/${id}`, data, {
            params: {
              ...params,
            },
          });

          commit('ajax', ajax);

          const { data: item } = await ajax;

          commit('item', item);
          commit('initialItem', item);

          commit('loaded', true);

          commit('ajax', null);
        } catch (e) {
          commit('ajax', null);

          const { request, response } = e;
          commit('error', { request, response });

          throw e;
        }
      },
      async destroy({ commit, dispatch, state }, id) {
        try {
          const ajax = Vue.axios.delete(`/${state.slug}/${id}`);

          commit('ajax', ajax);

          const { data: deleted } = await ajax;

          commit('deleted', deleted);

          commit('ajax', null);

          await dispatch('loadUser', null, { root: true });
        } catch (e) {
          commit('ajax', null);

          const { request, response } = e;
          commit('error', { request, response });

          throw e;
        }
      },
      ...actions,
    },
  };
}
