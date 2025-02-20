import Vue from "vue";

import { extractErrors } from "@/helpers";

import RestModule from "../RestModule";

export default new RestModule(
  "loans",
  {
    params: {
      order: "-id",
      page: 1,
      per_page: 10,
    },
    exportFields: [
      "id",
      "status",
      "departure_at",
      "duration_in_minutes",
      "estimated_distance",
      "estimated_insurance",
      "estimated_price",
      "message_for_owner",
      "reason",
      "borrower.id",
      "borrower.user.id",
      "borrower.user.name",
      "borrower.user.last_name",
      "loanable.id",
      "loanable.name",
      "loanable.community.name",
      "loanable.type",
      "loanable.owner.id",
      "loanable.owner.user.id",
      "loanable.owner.user.name",
      "loanable.owner.user.last_name",
      "loanable.owner.user.main_community.name",
      "intention.id",
      "intention.message_for_borrower",
      "intention.status",
      "handover.id",
      "handover.purchases_amount",
      "final_price",
      "final_insurance",
      "final_platform_tip",
      "final_distance",
      "takeover.mileage_beginning",
      "handover.mileage_end",
      "calendar_days",
      "canceled_at",
    ],
  },
  {
    async cancel({ commit }, loanId) {
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();

      try {
        commit("cancelToken", cancelToken);
        await Vue.axios.put(`/loans/${loanId}/cancel`, null, { cancelToken: cancelToken.token });

        commit("cancelToken", null);
      } catch (e) {
        commit("cancelToken", null);

        const { request, response } = e;
        commit("error", { request, response });

        throw e;
      }
    },
    async cancelAction({ commit }, action) {
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();

      try {
        commit("cancelToken", cancelToken);
        await Vue.axios.put(`/loans/${action.loan_id}/actions/${action.id}/cancel`, action, {
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
    async completeAction({ commit }, { action, type = "actions" }) {
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();

      try {
        commit("cancelToken", cancelToken);
        await Vue.axios.put(`/loans/${action.loan_id}/${type}/${action.id}/complete`, action, {
          cancelToken: cancelToken.token,
        });

        commit("cancelToken", null);
      } catch (e) {
        commit("cancelToken", null);

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
                },
                { root: true }
              );
              break;
            default:
              throw e;
          }
        } else {
          throw e;
        }
      }
    },
    async createAction({ commit }, action) {
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();

      try {
        commit("cancelToken", cancelToken);
        await Vue.axios.post(`/loans/${action.loan_id}/actions`, action, {
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
    async test({ commit }, params) {
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();

      try {
        commit("cancelToken", cancelToken);
        const { data } = await Vue.axios.get(`/loanables/${params.loanable_id}/test`, {
          params,
          cancelToken: cancelToken.token,
        });

        commit("mergeItem", {
          estimated_insurance: data.insurance,
          estimated_price: data.price,
          actual_price: data.price,
          loanable: {
            ...data,
          },
        });

        commit("cancelToken", null);
      } catch (e) {
        commit("cancelToken", null);
        if (!e.message || e.message !== "loans canceled test") {
          throw e;
        }
      }
    },
    async isAvailable({ commit }, loanId) {
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();

      try {
        commit("cancelToken", cancelToken);

        const { data } = await Vue.axios.get(`/loans/${loanId}/isavailable`, {
          loanId,
          cancelToken: cancelToken.token,
        });

        commit("patchItem", {
          isAvailable: data.isAvailable,
        });

        commit("cancelToken", null);
      } catch (e) {
        commit("cancelToken", null);
        throw e;
      }
    },
    async rejectAction({ commit }, action) {
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();

      try {
        commit("cancelToken", cancelToken);
        await Vue.axios.put(`/loans/${action.loan_id}/actions/${action.id}/reject`, action, {
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
    async validate({ commit }, { loan, user }) {
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();
      try {
        const { data } = await Vue.axios.put(`/loans/${loan.id}/validate`, null, cancelToken);
        commit("cancelToken", null);

        if (user.id === loan.borrower.user.id) {
          commit("patchItem", {
            borrower_validated_at: data,
          });
        }
        if (user.id === loan.loanable.owner.id) {
          commit("patchItem", {
            owner_validated_at: data,
          });
        }
      } catch (e) {
        commit("cancelToken", null);
        commit(
          "addNotification",
          {
            content: extractErrors(e.response.data).join(",") ?? "Erreur serveur",
            title: "Impossible de valider l'emprunt.",
            variant: "danger",
          },
          { root: true }
        );
        throw e;
      }
    },
    async updateMileage({ commit, state }, { action, type = "actions" }) {
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();

      try {
        commit("cancelToken", cancelToken);
        await Vue.axios.put(`/loans/${action.loan_id}/${type}/${action.id}/update_mileage`, action, {
          cancelToken: cancelToken.token,
        }).then(response => {

          // update loan information following mileage update
          let expenses = [...state.item.expenses];
          if( response.data.loan.expenses ) {
            expenses[0].amount = response.data.loan.expenses[0].amount;
          }
          let handover = {...state.item.handover};
          handover.mileage_end = response.data.loan.handover.mileage_end

          commit('item', {
            ...state.item,
            final_distance: response.data.loan.final_distance,
            final_price: response.data.loan.final_price,
            expenses,
            handover,
          });
        });
        commit("cancelToken", null);
      } catch (e) {
        commit("cancelToken", null);

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
                },
                { root: true }
              );
              break;
            default:
              throw e;
          }
        } else {
          throw e;
        }
      }
    },
  }
);
