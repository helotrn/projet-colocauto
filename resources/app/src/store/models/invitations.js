import RestModule from "../RestModule";
import Vue from "vue";

export default new RestModule(
  "invitations",
  {
    params: {
      order: "-id",
      page: 1,
      per_page: 10,
      q: "",
      type: null,
    },
  },
  {
    async resend({ commit, dispatch, state }, id) {
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();

      try {
        commit("cancelToken", cancelToken);
        await Vue.axios.post(`/${state.slug}/${id}/resend`, {}, {
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
  }
);
