import RestModule from "../RestModule";

export default new RestModule(
  "invitations",
  {
    params: {
      page: 1,
      per_page: 10,
      q: "",
      type: null,
    },
  }
);
