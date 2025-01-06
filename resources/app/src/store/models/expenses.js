import RestModule from "../RestModule";
import Vue from "vue";

export default new RestModule(
  "expenses",
  {
    params: {
      page: 1,
      per_page: 10,
    },
    exportFields: [
      "id",
      "name",
      "amount",
      "user.full_name",
      "loanable.name",
      "executed_at",
      "tag.name",
    ],
  },
  {
    // treat expense update as a particular case
    async updateItem({ dispatch, state }, params) {
      await dispatch("update", { id: state.item.id, data: {
        ...state.item,
        // do not send those parameters on update
        changes: undefined,
        loan: undefined,
        loanable: undefined,
      }, params });
    },
  },
  {
    setFilterParams(state, params){
      // cancel other filters
      Vue.set(state.params, 'user.id', undefined);
      Vue.set(state.params, 'loanable.id', undefined);
      Vue.set(state.params, 'executed_at', undefined);

      Object.keys(params).forEach(key => {
        Vue.set(state.params, key, params[key]);
      });
    }
  }
);
