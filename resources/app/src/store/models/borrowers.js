import RestModule from "../RestModule";

export default new RestModule("borrowers", {
  params: {
    order: "id",
    page: 1,
    per_page: 10,
    q: "",
    type: null,
    deleted_at: null,
  },
});
