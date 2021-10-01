import Vue from "vue";
import RestModule from "../RestModule";

export default new RestModule(
  "loanables",
  {
    params: {
      order: "name",
      page: 1,
      per_page: 10,
      q: "",
      type: null,
      deleted_at: null,
    },
    exportFields: [
      "id",
      "name",
      "type",
      "comments",
      "instructions",
      "location_description",
      "position",
      "owner.id",
      "owner.user.id",
      "owner.user.name",
      "owner.user.last_name",
      "community.id",
      "community.name",
      "car_insurer",
    ],
    exportNotFields: ["events"],
  },
  {
    async disable({ commit, dispatch, state }, id) {
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();

      try {
        commit("cancelToken", cancelToken);
        const response = await Vue.axios.delete(`/${state.slug}/${id}`, {cancelToken});

        commit("deleted", response.data);

        commit("cancelToken", null);

        await dispatch("loadUser", null, { root: true });
      } catch (e) {
        commit("cancelToken", null);

        const { request, response } = e;
        commit("error", { request, response });

        throw e;
      }
    },
    reset({ commit, state }) {
      const newData = state.data.map((d) => ({
        ...d,
        available: null,
        insurance: null,
        price: null,
        pricing: null,
        tested: false,
      }));

      commit("data", newData);
    },
    async testAll({ commit, state }, { loan, communityId }) {
      try {
        const responses = await Promise.all([
          ...state.data.map((d) =>
            Vue.axios.get(`/${state.slug}/${d.id}/test`, {
              params: { ...loan, loanable_id: d.id, community_id: communityId },
            })
          ),
        ]);

        const newData = state.data.map((d, index) => ({
          ...d,
          ...responses[index].data,
          tested: true,
        }));

        commit("data", newData);

      } catch (e) {
        const { request, response } = e;
        commit("error", { request, response });

        throw e;
      }
    },
    async testOne({ commit, state }, { communityId, loan, loanableId }) {
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();

      try {
        commit("cancelToken", cancelToken);
        const response = await Vue.axios.get(`/${state.slug}/${loanableId}/test`, {
          params: {
            ...loan,
            loanable_id: loanableId,
            community_id: communityId,
          },
          cancelToken
        });

        const newData = state.data.map((d) => {
          if (d.id === loanableId) {
            return {
              ...d,
              ...response.data,
              tested: true,
            };
          }

          return d;
        });

        commit("data", newData);

        commit("cancelToken", null);
      } catch (e) {
        commit("cancelToken", null);
        commit(
          "addNotification",
          {
            content: JSON.stringify(e),
            title: `Erreur de test pour ${state.slug}`,
            variant: "danger",
            type: "ajax",
          },
          { root: true }
        );
        const { request, response } = e;
        commit("error", { request, response });

        throw e;
      }
    },
  }
);
