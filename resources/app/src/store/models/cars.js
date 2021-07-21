import RestModule from "../RestModule";

export default new RestModule(
  "cars",
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
