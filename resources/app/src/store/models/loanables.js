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
      try {
        const ajax = Vue.axios.delete(`/${state.slug}/${id}`);

        commit("ajax", ajax);

        const { data: deleted } = await ajax;

        commit("deleted", deleted);

        commit("ajax", null);

        await dispatch("loadUser", null, { root: true });
      } catch (e) {
        commit("ajax", null);

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
        const ajax = Promise.all([
          ...state.data.map((d) =>
            Vue.axios.get(`/${state.slug}/${d.id}/test`, {
              params: { ...loan, loanable_id: d.id, community_id: communityId },
            })
          ),
        ]);

        commit("ajax", ajax);

        const data = await ajax;

        const newData = state.data.map((d, index) => ({
          ...d,
          ...data[index].data,
          tested: true,
        }));

        commit("data", newData);

        commit("ajax", null);
      } catch (e) {
        commit("ajax", null);

        const { request, response } = e;
        commit("error", { request, response });

        throw e;
      }
    },
    async testOne({ commit, state }, { communityId, loan, loanableId }) {
      try {
        const ajax = Vue.axios.get(`/${state.slug}/${loanableId}/test`, {
          params: {
            ...loan,
            loanable_id: loanableId,
            community_id: communityId,
          },
        });

        commit("ajax", ajax);

        const { data } = await ajax;

        const newData = state.data.map((d) => {
          if (d.id === loanableId) {
            return {
              ...d,
              ...data,
              tested: true,
            };
          }

          return d;
        });

        commit("data", newData);

        commit("ajax", null);
      } catch (e) {
        commit("ajax", null);

        const { request, response } = e;
        commit("error", { request, response });

        throw e;
      }
    },
  }
);
