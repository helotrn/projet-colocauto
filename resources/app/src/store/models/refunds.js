import RestModule from "../RestModule";
import Vue from "vue";

export default new RestModule(
  "refunds",
  {
    params: {
      page: 1,
      per_page: 10,
    },
    exportFields: [
      "id",
      "amount",
      "user.full_name",
      "credited_user.full_name",
      "executed_at",
    ],
  },
  {},
  {
    setFilterParams(state, params){
      // cancel other filters
      Vue.set(state.params, 'user.id', undefined);
      Vue.set(state.params, 'credited_user.id', undefined);
      Vue.set(state.params, 'executed_at', undefined);

      Object.keys(params).forEach(key => {
        Vue.set(state.params, key, params[key]);
      });
    }
  }
);
