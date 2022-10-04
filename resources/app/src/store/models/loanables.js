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
    // Export fields should be correlated with that of app/Exports/LoanableExport.php
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
      "owner.user.communities.id",
      "owner.user.communities.name",
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

        await dispatch("loadLoanables", null, { root: true });
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
        const { data } = await Vue.axios.get(`/loanables/search`, {
          params: { ...loan },
          cancelToken: cancelToken.token,
        });

        const availableLoanable = new Map(
          Object.values(data).map((estimatedLoan) => [estimatedLoan.loanableId, estimatedLoan])
        );

        const newData = state.data.map((d) => {
          const isAvailable = availableLoanable.has(d.id);
          return {
            ...d,
            available: isAvailable,
            estimatedCost: isAvailable ? availableLoanable.get(d.id).estimatedCost : null,
            tested: true,
          };
        });

        commit("data", newData);
      } catch (e) {
        const { request, response } = e;
        if (request) {
          switch (request.status) {
            case 422:
              commit(
                "addNotification",
                {
                  content: extractErrors(response.data).join(", "),
                  title: "Erreur de validation",
                  variant: "danger",
                  type: "extension",
                },
                { root: true }
              );
              return;
            default:
              break;
          }
        }
        commit("error", { request, response });

        throw e;
      } finally {
        commit("cancelToken", null);
      }
    },
  }
);
