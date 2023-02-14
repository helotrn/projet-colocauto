import RestModule from "../RestModule";
import Vue from "vue";

export default new RestModule(
  "expenses",
  {
    params: {
      page: 1,
      per_page: 10,
    },
  },
  {},
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
