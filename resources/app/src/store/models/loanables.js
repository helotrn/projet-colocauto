import Vue from "vue";

import { extractErrors } from "@/helpers";

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
      "community_ids",
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
        const { data: deleted } = await Vue.axios.delete(`/${state.slug}/${id}`, {
          cancelToken: cancelToken.token,
        });

        commit("deleted", deleted);

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
    async search({ commit, state }, { loan }) {
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();

      try {
        commit("cancelToken", cancelToken);
        const { data } = await Vue.axios.get(`/${state.slug}/search`, {
          params: { ...loan },
          cancelToken: cancelToken.token,
        });

        const availableLoanable = new Map(
          Object.values(data).map((estimatedLoan) => [estimatedLoan.loanable.id, estimatedLoan])
        );

        const newData = state.data.map((d) => {
          const isAvaialble = availableLoanable.has(d.id);
          return {
            ...d,
            available: isAvaialble,
            estimatedCost: isAvaialble ? availableLoanable.get(d.id).estimatedCost : null,
            tested: true,
          };
        });

        commit("data", newData);
      } catch (e) {
        const { request, response } = e;
        commit("error", { request, response });

        throw e;
      } finally {
        commit("cancelToken", null);
      }
    },
  }
);
