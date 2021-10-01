import Vue from "vue";
import merge from "deepmerge";

export default function RestModule(slug, initialState, actions = {}, mutations = {}) {
  return {
    namespaced: true,
    state: {
      axiosCancelSource: null,
      data: [],
      deleted: null,
      empty: null,
      error: null,
      exportFields: ["id"],
      exportNotFields: [],
      exportUrl: null,
      filters: {},
      form: null,
      initialItem: "",
      item: null,
      lastLoadedAt: null,
      lastPage: 1,
      loaded: false,
      loading: false,
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
        state.initialItem = JSON.stringify(item);
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
  
        try{
          commit("cancelToken", cancelToken);
          const response = await Vue.axios.get(`/${state.slug}`, {
            params: {
              ...params,
              q,
              cancelToken
            },
          });
          commit("search", data);
          commit("cancelToken", null);
        }catch(e){
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
          const response = await Vue.axios.post(`/${state.slug}`, data, {
            params: {
              ...params,
              cancelToken
            },
          });

          commit("item", response.data);
          commit("initialItem", response.data);
          commit("cancelToken", cancelToken);

          dispatch("retrieve", state.params);
        } catch (e) {
          commit("cancelToken", cancelToken);

          const { request, response } = e;
          commit("error", { request, response });

          throw e;
        }

        commit("loading", false);
      },
      async options({ state, commit }) {
        if (state.form === null || state.filters === null || state.empty === null) {
          const options = Vue.axios.options(`/${state.slug}`);

          const {
            data: { item: empty, filters, form },
          } = await options;

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
          const response = await Vue.axios.get(`/${state.slug}/${id}`, {
            params: {
              ...params,
              cancelToken
            },
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
      async retrieve({ dispatch, state, commit }, params) {
        const { CancelToken } = Vue.axios;
        const cancelToken = CancelToken.source();
  
        commit("loaded", false);

        try {
          await dispatch("options");
          commit("axiosCancelSource", source);
          const response = await Vue.axios.get(`/${state.slug}`, {
            params: {
              ...state.params,
              ...params,
            },
            cancelToken,  
          });

          commit("data", response.data);
          commit("total", response.total);
          commit("lastPage", response.lastPage);
          commit("lastLoadedAt", Date.now());

          commit("loaded", true);

          commit("axiosCancelSource", null);
        } catch (e) {
          commit("axiosCancelSource", null);

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
              cancelToken
            },
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
          const response = await Vue.axios.delete(`/${state.slug}/${id}`, {cancelToken});

          commit("deleted", response.data);
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
          const response = await Vue.axios.put(`/${state.slug}/${id}/restore`, null, {cancelToken});

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
        
        try {
          const response = await Vue.axios.get(`/${state.slug}`, {
            params: {
              ...state.params,
              ...params,
              per_page: 1000000,
              page: 1,
              fields: state.exportFields.join(","),
              "!fields": state.exportNotFields.join(","),
              cancelToken
            },
            headers: {
              Accept: "text/csv",
            },
          });

          commit("exportUrl", response.data);

          commit("cancelToken", null);
        } catch (e) {
          commit("cancelToken", null);

          const { request, response } = e;
          commit("error", { request, response });

          throw e;
        }
      },
      ...actions,
    },
  };
}
