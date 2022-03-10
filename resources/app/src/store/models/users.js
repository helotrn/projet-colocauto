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
        const { data } = await Vue.axios.put(`/users/${userId}/borrower/approve`, null, {
          cancelToken: cancelToken.token,
        });

        commit("mergeItem", { borrower: data });

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
        const response = await Vue.axios.put(`/users/${userId}/communities/${communityId}`, null, {
          cancelToken: cancelToken.token,
        });

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
        const { data } = await Vue.axios.put(`/users/${userId}/borrower/suspend`, null, {
          cancelToken: cancelToken.token,
        });

        commit("mergeItem", { borrower: data });

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
        const { data } = await Vue.axios.delete(`/users/${userId}/borrower/suspend`, {
          cancelToken: cancelToken.token,
        });

        commit("mergeItem", { borrower: data });

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
        const { data } = await Vue.axios.post(
          `/users/${userId}/email`,
          {
            password: currentPassword,
            email: newEmail,
          },
          { cancelToken: cancelToken.token }
        );

        commit("mergeItem", data);

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
        await Vue.axios.post(
          `/users/${userId}/password`,
          {
            current: currentPassword,
            new: newPassword,
          },
          { cancelToken: cancelToken.token }
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
        const { data: item } = await Vue.axios.put(`/${state.slug}/${id}`, data, {
          params: {
            ...params,
          },
          cancelToken: cancelToken.token,
        });

        // If the user currently being updated is the logged-in user
        // (rootState.user), then update it's state as well.
        // Only diff with the update method in RestModule.
        if (rootState.user.id === item.id) {
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
