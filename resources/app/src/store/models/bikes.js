import RestModule from "../RestModule";

export default new RestModule(
  "bikes",
  {
    params: {
      order: "name",
      page: 1,
      per_page: 10,
      q: "",
      type: null,
      deleted_at: null,
    },
  },
  {
    options() {},
  }
);
