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
      "loanable.type",
      "loanable.owner.id",
      "loanable.owner.user.id",
      "loanable.owner.user.name",
      "loanable.owner.user.last_name",
      "intention.id",
      "intention.message_for_borrower",
      "intention.status",
      "handover.id",
      "handover.purchases_amount",
      "final_price",
      "final_insurance",
      "final_platform_tip",
      "calendar_days",
      "canceled_at",
    ],
  },
  {
    async cancel({ commit }, loanId) {
      try {
        const ajax = Vue.axios.put(`/loans/${loanId}/cancel`);

        commit("ajax", ajax);

        await ajax;

        commit("ajax", null);
      } catch (e) {
        commit("ajax", null);

        const { request, response } = e;
        commit("error", { request, response });

        throw e;
      }
    },
    async cancelAction({ commit }, action) {
      try {
        const ajax = Vue.axios.put(`/loans/${action.loan_id}/actions/${action.id}/cancel`, action);

        commit("ajax", ajax);

        await ajax;

        commit("ajax", null);
      } catch (e) {
        commit("ajax", null);

        const { request, response } = e;
        commit("error", { request, response });

        throw e;
      }
    },
    async completeAction({ commit }, action) {
      try {
        const ajax = Vue.axios.put(
          `/loans/${action.loan_id}/actions/${action.id}/complete`,
          action
        );

        commit("ajax", ajax);

        await ajax;

        commit("ajax", null);
      } catch (e) {
        commit("ajax", null);

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
      try {
        const ajax = Vue.axios.post(`/loans/${action.loan_id}/actions`, action);

        commit("ajax", ajax);

        await ajax;

        commit("ajax", null);
      } catch (e) {
        commit("ajax", null);

        const { request, response } = e;
        commit("error", { request, response });

        throw e;
      }
    },
    async test({ commit }, params) {
      const { CancelToken } = Vue.axios;
      let cancel;

      try {
        const ajax = Vue.axios.get(`/loanables/${params.loanable_id}/test`, {
          params,
          cancelToken: new CancelToken((c) => {
            cancel = c;
          }),
        });
        ajax.cancel = cancel;
        ajax.context = "test";

        commit("ajax", ajax);

        const { data } = await ajax;

        commit("mergeItem", {
          estimated_insurance: data.insurance,
          estimated_price: data.price,
          loanable: {
            ...data,
          },
        });

        commit("ajax", null);
      } catch (e) {
        if (!e.message || e.message !== "loans canceled test") {
          throw e;
        }
      }
    },
  }
);
