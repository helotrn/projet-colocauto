import Vue from "vue";
import merge from "deepmerge";

export default function RestModule(slug, initialState, actions = {}, mutations = {}) {
  return {
    namespaced: true,
    state: {
      cancelToken: null,
      data: [],
      deleted: null,
      empty: null,
      error: null,
      exportFields: ["id"],
      exportNotFields: [],
      exportUrl: null,
      filters: {},
      form: null,
      // Used in forms to determine whether a field has changed or to reset it's content.
      initialItemJson: "",
      item: null,
      lastLoadedAt: null,
      lastPage: 1,
      loaded: false,
      loading: false,
      generatingCSV: false,
      search: [],
      lastSearchQuery: "",
      params: {
        page: 1,
        per_page: 10,
        q: "",
        order: "id",
      },
      slug,
      total: undefined,
      ...initialState,
    },
    getters: {
      initialItem(state) {
        return JSON.parse(state.initialItemJson);
      },
      initialItemJson(state) {
        return state.initialItemJson;
      },
    },
    mutations: {
      addData(state, data) {
        state.data.push(...data);
      },
      cancelToken(state, cancelToken) {
        if (cancelToken && state.cancelToken) {
          state.cancelToken.cancel(`${state.slug} canceled`);
        }
        state.cancelToken = cancelToken;
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
      exportUrl(state, exportUrl) {
        state.exportUrl = exportUrl;
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
        state.initialItemJson = JSON.stringify(item);
      },
      lastLoadedAt(state, lastLoadedAt) {
        state.lastLoadedAt = lastLoadedAt;
      },
      lastPage(state, lastPage) {
        state.lastPage = lastPage;
      },
      loaded(state, loaded) {
        state.loaded = loaded;
      },
      loading(state, loading) {
        state.loading = loading;
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
      search(state, search) {
        state.search = search;
      },
      setOrder(state, { field, direction }) {
        switch (direction) {
          case "desc":
            state.params.order = `-${field}`;
            break;
          case "asc":
          default:
            state.params.order = `${field}`;
            break;
        }
      },
      setParam(state, { name, value }) {
        Vue.set(state.params, name, value);
      },
      total(state, total) {
        state.total = total;
      },
      generatingCSV(state, value) {
        state.generatingCSV = value;
      },
      ...mutations,
    },
    actions: {
      async load({ dispatch, state }) {
        await dispatch("retrieve", state.params);
      },
      async loadEmpty({ commit, dispatch, state }) {
        commit("loaded", false);

        try {
          await dispatch("options");

          commit("item", JSON.parse(JSON.stringify(state.empty)));
          commit("initialItem", state.item);

          commit("loaded", true);
        } catch (e) {
          const { request, response } = e;
          commit("error", { request, response });

          throw e;
        }
      },
      async search({ commit, state }, { q, params }) {
        const { CancelToken } = Vue.axios;
        const cancelToken = CancelToken.source();

        try {
          commit("cancelToken", cancelToken);
          const {
            data: { data },
          } = await Vue.axios.get(`/${state.slug}`, {
            params: {
              ...params,
              q,
            },
            cancelToken: cancelToken.token,
          });
          commit("search", data);
          commit("cancelToken", null);
        } catch (e) {
          commit("cancelToken", null);
        }
      },
      async createItem({ dispatch, state }, params) {
        await dispatch("create", { data: state.item, params });
      },
      async create({ commit, dispatch, state }, { data, params }) {
        commit("loaded", false);
        commit("loading", true);
        const { CancelToken } = Vue.axios;
        const cancelToken = CancelToken.source();

        try {
          commit("cancelToken", cancelToken);
          const { data: item } = await Vue.axios.post(`/${state.slug}`, data, {
            params: {
              ...params,
            },
            cancelToken: cancelToken.token,
          });

          commit("item", item);
          commit("initialItem", item);
          commit("cancelToken", null);

          dispatch("retrieve", state.params);
        } catch (e) {
          commit("cancelToken", null);
          const { request, response } = e;
          commit("error", { request, response });

          throw e;
        } finally {
          commit("cancelToken", null);
          commit("loading", false);
        }
      },
      async options({ state, commit }) {
        if (state.form === null || state.filters === null || state.empty === null) {
          const {
            data: { item: empty, filters, form },
          } = await Vue.axios.options(`/${state.slug}`);

          commit("empty", empty);
          commit("filters", filters);
          commit("form", form);
        }
      },
      async retrieveOne({ dispatch, commit, state }, { params, id }) {
        commit("loaded", false);
        const { CancelToken } = Vue.axios;
        const cancelToken = CancelToken.source();

        try {
          await dispatch("options");

          commit("cancelToken", cancelToken);
          const { data } = await Vue.axios.get(`/${state.slug}/${id}`, {
            params: {
              ...params,
            },
            cancelToken: cancelToken.token,
          });

          commit("item", data);
          commit("initialItem", data);

          commit("loaded", true);

          commit("cancelToken", null);
        } catch (e) {
          commit("cancelToken", null);

          const { request, response } = e;
          commit("error", { request, response });

          throw e;
        }
      },
      async retrieve({ dispatch, state, commit }, params) {
        const { CancelToken } = Vue.axios;
        const cancelToken = CancelToken.source();

        commit("loaded", false);
        try {
          await dispatch("options");
          commit("cancelToken", cancelToken);
          const {
            data: { data, total, last_page },
          } = await Vue.axios.get(`/${state.slug}`, {
            params: {
              ...state.params,
              ...params,
            },
            cancelToken: cancelToken.token,
          });

          commit("data", data);
          commit("total", total);
          commit("lastPage", last_page);
          commit("lastLoadedAt", Date.now());

          commit("loaded", true);

          commit("cancelToken", null);
        } catch (e) {
          commit("cancelToken", null);

          const { request, response } = e;
          commit("error", { request, response });

          throw e;
        }
      },
      async updateItem({ dispatch, state }, params) {
        await dispatch("update", { id: state.item.id, data: state.item, params });
      },
      async update({ commit, state }, { id, data, params }) {
        commit("loaded", false);
        const { CancelToken } = Vue.axios;
        const cancelToken = CancelToken.source();

        try {
          commit("cancelToken", cancelToken);
          const response = await Vue.axios.put(`/${state.slug}/${id}`, data, {
            params: {
              ...params,
            },
            cancelToken: cancelToken.token,
          });

          commit("item", response.data);
          commit("initialItem", response.data);

          commit("loaded", true);

          commit("cancelToken", null);
        } catch (e) {
          commit("cancelToken", null);

          const { request, response } = e;
          commit("error", { request, response });

          throw e;
        }
      },
      async destroy({ commit, state }, id) {
        const { CancelToken } = Vue.axios;
        const cancelToken = CancelToken.source();

        try {
          commit("cancelToken", cancelToken);
          const { data } = await Vue.axios.delete(`/${state.slug}/${id}`, {
            cancelToken: cancelToken.token,
          });

          commit("deleted", data);
          commit("cancelToken", null);
        } catch (e) {
          commit("cancelToken", null);

          const { request, response } = e;
          commit("error", { request, response });

          throw e;
        }
      },
      async restore({ commit, state }, id) {
        const { CancelToken } = Vue.axios;
        const cancelToken = CancelToken.source();

        try {
          commit("cancelToken", cancelToken);
          await Vue.axios.put(`/${state.slug}/${id}/restore`, null, {
            cancelToken: cancelToken.token,
          });

          commit("cancelToken", null);
        } catch (e) {
          commit("cancelToken", null);

          const { request, response } = e;
          commit("error", { request, response });

          throw e;
        }
      },
      async export({ state, commit }, params) {
        const { CancelToken } = Vue.axios;
        const cancelToken = CancelToken.source();
        commit("generatingCSV", true);

        try {
          const { data } = await Vue.axios.get(`/${state.slug}`, {
            params: {
              ...state.params,
              ...params,
              per_page: 1000000,
              page: 1,
              fields: state.exportFields.join(","),
              "!fields": state.exportNotFields.join(","),
            },
            cancelToken: cancelToken.token,
            headers: {
              Accept: "text/csv",
            },
          });

          commit("exportUrl", data);

          commit("cancelToken", null);
          commit("generatingCSV", false);
        } catch (e) {
          commit("cancelToken", null);
          commit("generatingCSV", false);

          const { request, response } = e;
          commit("error", { request, response });

          throw e;
        }
      },
      ...actions,
    },
  };
}
