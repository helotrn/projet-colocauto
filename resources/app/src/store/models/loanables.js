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
    async search({ commit, state }, payload) {
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();

      try {
        commit("cancelToken", cancelToken);
        
        // case of a loan search
        if( payload.loan ){
          const { data } = await Vue.axios.get(`/loanables/search`, {
            params: { ...payload.loan },
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
        } else {
          // case of a classic search to fill an input
          const {
            data: { data },
          } = await Vue.axios.get(`/${state.slug}`, {
            params: {
              ...payload.params,
              q: payload.q,
            },
            cancelToken: cancelToken.token,
          });
          commit("search", data);
        }

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
    async list({ commit, state }, { types }) {
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();
      commit("loaded", false);
      commit("loading", true);
      try {
        commit("cancelToken", cancelToken);
        const { data } = await Vue.axios.get(`/loanables/list`, {
          params: { types },
          cancelToken: cancelToken.token,
        });

        const newData = (data.bikes || [])
          .map((bike) => ({
            type: "bike",
            ...bike,
          }))
          .concat(
            (data.cars || []).map((car) => ({
              type: "car",
              ...car,
            })),
            (data.trailers || []).map((trailer) => ({
              type: "trailer",
              ...trailer,
            }))
          );

        commit("data", newData);
        commit("loaded", true);
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
        commit("loading", false);
        commit("cancelToken", null);
      }
    },
    async addCoowner({ commit, state }, { loanable, user }) {
      const { data } = await Vue.axios.put(
        `/loanables/${loanable.id}/coowners`,
        {
          user_id: user.id,
        },
      );
      const coowners = [
        ...(state.item.coowners ?? []),
        {
          ...data,
          loanable,
          user,
        },
      ];

      commit("patchItem", { coowners });
      commit("patchInitialItem", { coowners });
      commit("addNotification", {
        content: user.full_name,
        title: "Copropriétaire ajouté·e !",
        variant: "success",
        type: "loanable",
      },{ root: true });
    },
    async removeCoowner({ commit, state }, { loanable, user, coownerId }) {
      await Vue.axios.delete(`/loanables/${loanable.id}/coowners`,
        {data: {
          user_id: user.id,
        }});
      const coowners = [...state.item.coowners.filter((c) => c.id !== coownerId)];
      commit("patchItem", { coowners });
      commit("patchInitialItem", { coowners });

      commit("addNotification", {
        content: user.full_name,
        title: "Copropriétaire retiré·e !",
        variant: "success",
        type: "loanable",
      },{ root: true });
    },

    // treat loanable update as a particular case
    async updateItem({ dispatch, state }, params) {
      await dispatch("update", { id: state.item.id, data: {
        ...state.item,
        // coowners must not be saved here but via add/removeCoowner
        coowners: undefined
      }, params });
    },
  }
);
