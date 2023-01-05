import RestModule from "../RestModule";

export default new RestModule("expense_tags", {
  params: {
    order: "name",
    page: 1,
    per_page: 10,
    q: "",
    type: null,
    deleted_at: null,
  },
});
