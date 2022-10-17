import LoanableSearch from "@/views/loanable/Search.vue";

export default [
  {
    path: "/search/:view",
    name: "loanable-list",
    component: LoanableSearch,
    // Get the ':view' as a prop
    props: true,
    meta: {
      auth: true,
      slug: "loanables",
      data: {
        loans: {
          loadEmpty: {},
        },
      },
    },
  },
];
