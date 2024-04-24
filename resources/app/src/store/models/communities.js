import Vue from "vue";
import RestModule from "../RestModule";

export default new RestModule(
  "communities",
  {
    params: {
      order: "name",
      page: 1,
      per_page: 10,
      q: "",
      type: null,
    },
    exportFields: ["id", "name", "type", "center", "area", "area_google"],
    usersExportFields: [
      "id",
      "created_at",
      "submitted_at",
      "address",
      "date_of_birth",
      "description",
      "email",
      "is_smart_phone",
      "last_name",
      "name",
      "other_phone",
      "password",
      "phone",
      "postal_code",
      "borrower.id",
      "borrower.drivers_license_number",
      "borrower.approved_at",
      "proof.url",
      "approved_at",
      "suspended_at",
      "tags.id",
      "tags.name",
      "tags.slug",
    ],
    usersExportUrl: null,
    admins: {
      data: [],
      total: 0,
    },
  },
  {
    async addUser({ commit }, { id, data }) {
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();

      try {
        commit("cancelToken", cancelToken);
        const response = await Vue.axios.post(`/communities/${id}/users`, data, {
          params: {
            fields: "*,communities.*",
          },
          cancelToken: cancelToken.token,
        });
        commit("users/addData", [response.data], { root: true });
        commit("cancelToken", null);
      } catch (e) {
        commit("cancelToken", null);
        const { request, response } = e;
        commit("error", { request, response });

        throw e;
      }
    },
    async addAdmin({ commit }, { id, data }) {
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();

      try {
        commit("cancelToken", cancelToken);
        const response = await Vue.axios.post(`/communities/${id}/admins`, data, {
          params: {
            fields: "*,administrable_communities.*",
          },
          cancelToken: cancelToken.token,
        });
        commit("adminAddUsers", [response.data]);
        commit("cancelToken", null);
      } catch (e) {
        commit("cancelToken", null);
        const { request, response } = e;
        commit("error", { request, response });

        throw e;
      }
    },
    async exportUsers({ state, commit }, params) {
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();

      try {
        commit("cancelToken", cancelToken);
        const { data: url } = await Vue.axios.get(`/${state.slug}/${state.item.id}/users`, {
          params: {
            ...state.params,
            ...params,
            per_page: 1000000,
            page: 1,
            fields: state.usersExportFields.join(","),
          },
          cancelToken: cancelToken.token,
          headers: {
            Accept: "text/csv",
          },
        });
        commit("usersExportUrl", url);

        commit("cancelToken", null);
      } catch (e) {
        commit("cancelToken", null);

        const { request, response } = e;
        commit("error", { request, response });

        throw e;
      }
    },
    async removeUser({ commit }, { id, userId }) {
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();

      try {
        commit("cancelToken", cancelToken);
        await Vue.axios.delete(`/communities/${id}/users/${userId}`, {
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
    async removeAdmin({ commit }, { id, userId }) {
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();

      try {
        commit("cancelToken", cancelToken);
        await Vue.axios.delete(`/communities/${id}/admins/${userId}`, {
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
    async updateUser({ commit, rootState }, { id, userId, data }) {
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();

      try {
        commit("cancelToken", cancelToken);
        await Vue.axios.put(`/communities/${id}/users/${userId}`, data, {
          params: {
            fields: "*,communities.*",
          },
          cancelToken: cancelToken.token,
        });
        commit("cancelToken", null);

        const userIndex = rootState.users.data.findIndex((u) => u.id === data.id);
        const newUserArray = [...rootState.users.data];
        newUserArray[userIndex] = data;

        commit("users/data", newUserArray, { root: true });
      } catch (e) {
        commit("cancelToken", null);
        const { request, response } = e;
        commit("error", { request, response });

        throw e;
      }
    },
    async setCommittee({ commit }, { communityId, tagId, userId }) {
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();

      try {
        commit("cancelToken", cancelToken);
        await Vue.axios.put(
          `/communities/${communityId}` + `/users/${userId}/tags/${tagId}`,
          null,
          {
            cancelToken: cancelToken.token,
          }
        );

        commit("cancelToken", null);
      } catch (e) {
        commit("cancelToken", null);

        const { request, response } = e;
        commit("error", { request, response });

        throw e;
      }
    },
    async unsetCommittee({ commit }, { communityId, tagId, userId }) {
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();

      try {
        commit("cancelToken", cancelToken);
        await Vue.axios.delete(`/communities/${communityId}` + `/users/${userId}/tags/${tagId}`, {
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
    async getAdmins({ state, commit }, params) {
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();

      try {
        commit("cancelToken", cancelToken);
        const { data } = await Vue.axios.get(`/${state.slug}/${state.item.id}/admins`, {
          params: {
            ...state.params,
            ...params,
          },
          cancelToken: cancelToken.token,
        });
        commit("adminUsers", data);

        commit("cancelToken", null);
      } catch (e) {
        commit("cancelToken", null);

        const { request, response } = e;
        commit("error", { request, response });

        throw e;
      }
    }
  },
  {
    usersExportUrl(state, usersExportUrl) {
      state.usersExportUrl = usersExportUrl;
    },
    adminUsers(state, users) {
      state.admins = users;
    },
    adminAddUsers(state, users) {
      state.admins.data.push(...users);
    },
    adminUsersData(state, data) {
      state.admins.data = data;
    },
  }
);
