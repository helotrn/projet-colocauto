import Vue from "vue";
import RestModule from "../RestModule";

export default new RestModule(
  "users",
  {
    params: {
      order: "name",
      page: 1,
      per_page: 10,
      q: "",
      type: null,
    },
    exportFields: [
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
      "borrower.drivers_licence_number",
      "borrower.approved_at",
      "communities.id",
      "communities.name",
      "communities.proof.url",
      "communities.approved_at",
      "communities.suspended_at",
      "communities.tags.id",
      "communities.tags.name",
      "communities.tags.slug",
      "payment_methods.id",
      "payment_methods.type",
      "payment_methods.credit_card_type",
      "payment_methods.external_id",
    ],
  },
  {
    async approveBorrower({ commit }, userId) {
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();

      try {
        commit("cancelToken", cancelToken);
        const response = await Vue.axios.put(
          `/users/${userId}/borrower/approve`,
          null,
          cancelToken
        );

        commit("mergeItem", { borrower: response.data });

        commit("cancelToken", null);
      } catch (e) {
        commit("cancelToken", null);

        const { request, response } = e;
        commit("error", { request, response });

        throw e;
      }
    },
    async joinCommunity({ commit }, { communityId, userId }) {
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();

      try {
        commit("cancelToken", cancelToken);
        const response = await Vue.axios.put(
          `/users/${userId}/communities/${communityId}`,
          null,
          cancelToken
        );

        commit("mergeItem", { communities: [response.data] });

        commit("cancelToken", null);
      } catch (e) {
        commit("cancelToken", null);

        const { request, response } = e;
        commit("error", { request, response });

        throw e;
      }
    },
    async suspendBorrower({ commit }, userId) {
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();

      try {
        commit("cancelToken", cancelToken);
        const response = await Vue.axios.put(`/users/${userId}/borrower/suspend`, null, {
          cancelToken,
        });

        commit("mergeItem", { borrower: response.data });

        commit("cancelToken", null);
      } catch (e) {
        commit("cancelToken", null);

        const { request, response } = e;
        commit("error", { request, response });

        throw e;
      }
    },
    async unsuspendBorrower({ commit }, userId) {
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();

      try {
        commit("cancelToken", cancelToken);
        const response = await Vue.axios.delete(`/users/${userId}/borrower/suspend`, {
          cancelToken,
        });

        commit("mergeItem", { borrower: response.data });

        commit("cancelToken", null);
      } catch (e) {
        commit("cancelToken", null);

        const { request, response } = e;
        commit("error", { request, response });

        throw e;
      }
    },
    async updateEmail({ commit }, { userId, currentPassword, newEmail }) {
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();

      try {
        commit("cancelToken", cancelToken);
        const response = await Vue.axios.post(
          `/users/${userId}/email`,
          {
            password: currentPassword,
            email: newEmail,
          },
          { cancelToken }
        );

        commit("mergeItem", response.data);

        commit("cancelToken", null);
      } catch (e) {
        commit("cancelToken", null);

        const { request, response } = e;
        commit("error", { request, response });

        throw e;
      }
    },
    async updatePassword({ commit }, { userId, currentPassword, newPassword }) {
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();

      try {
        commit("cancelToken", cancelToken);
        const response = await Vue.axios.post(
          `/users/${userId}/password`,
          {
            current: currentPassword,
            new: newPassword,
          },
          { cancelToken }
        );

        commit("cancelToken", null);
      } catch (e) {
        commit("cancelToken", null);

        const { request, response } = e;
        commit("error", { request, response });

        throw e;
      }
    },
    async update({ commit, state, rootState }, { id, data, params }) {
      commit("loaded", false);
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();

      try {
        commit("cancelToken", cancelToken);
        const response = await Vue.axios.put(`/${state.slug}/${id}`, data, {
          params: {
            ...params,
          },
          cancelToken,
        });

        if (rootState.user.id === response.data.id) {
          commit("user", { ...item }, { root: true });
        }
        commit("item", item);
        commit("initialItem", item);

        commit("loaded", true);

        commit("cancelToken", null);
      } catch (e) {
        commit("cancelToken", null);

        const { request, response } = e;
        commit("error", { request, response });

        throw e;
      }
    },
  }
);
