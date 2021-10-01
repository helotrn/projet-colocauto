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
      "opt_in_newsletter",
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
  },
  {
    async addUser({ commit }, { id, data }) {
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();

      try {
        commit("axiosCancelSource", source);
        const response = await Vue.axios.post(`/communities/${id}/users`, data, {
          params: {
            fields: "*,communities.*",
          },
        });
        commit('users/addData', [response.data], { root: true });
        commit("axiosCancelSource", null);
      } catch (e) {
        commit("axiosCancelSource", null);
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
        const response = await Vue.axios.get(`/${state.slug}/${state.item.id}/users`, {
          params: {
            ...state.params,
            ...params,
            per_page: 1000000,
            page: 1,
            fields: state.usersExportFields.join(","),
            cancelToken
          },
          headers: {
            Accept: "text/csv",
          },
        });
        commit("usersExportUrl", response.data);

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
        const response = await Vue.axios.delete(`/communities/${id}/users/${userId}`, { cancelToken });

        commit("cancelToken", null);
      } catch (e) {
        commit("cancelToken", null);

        const { request, response } = e;
        commit("error", { request, response });

        throw e;
      }
    },
    async updateUser({ commit, state }, { id, userId, data }) {
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();

      try {
        const response = await Vue.axios.put(`/communities/${id}/users/${userId}`, data, {
          params: {
            fields: "*,communities.*",
            cancelToken
          },
        });

        const userIndex = state.users.data.findIndex((u) => u.id === data.id);
        const newUserArray = [...state.users.data];
        newUserArray[userIndex] = data;

        commit("users/data", {
          newUserArray
        })

      } catch (e) {
        const { request, response } = e;
        commit("error", { request, response });

        throw e;
      }
    },
    async setCommittee({ commit }, { communityId, tagId, userId }) {
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();

      try {
        const response = await Vue.axios.put(
          `/communities/${communityId}` + `/users/${userId}/tags/${tagId}`, null, { cancelToken }
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
        const response = await Vue.axios.delete(
          `/communities/${communityId}` + `/users/${userId}/tags/${tagId}`
        );

        commit("cancelToken", null);
      } catch (e) {
        commit("cancelToken", null);

        const { request, response } = e;
        commit("error", { request, response });

        throw e;
      }
    },
  },
  {
    usersExportUrl(state, usersExportUrl) {
      state.usersExportUrl = usersExportUrl;
    },
  }
);
