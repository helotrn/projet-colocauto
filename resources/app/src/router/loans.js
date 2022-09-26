import Loan from "@/views/Loan.vue";

export default [
  {
    path: "/loans/:id",
    component: Loan,
    props: true,
    meta: {
      auth: true,
      slug: "loans",
      skipCleanup(to) {
        return to.name === "community-view";
      },
      data: {
        loans: {
          options: {},
        },
      },
      params: {
        fields: [
          "*",
          "total_estimated_cost",
          "actions.*",
          "borrower.id",
          "borrower.user.avatar",
          "borrower.user.name",
          "borrower.user.last_name",
          "borrower.user.full_name",
          "borrower.user.phone",
          "extensions.*",
          "handover.*",
          "handover.image.*",
          "handover.expense.*",
          "incidents.*",
          "intention.*",
          "loanable.name",
          "loanable.community.name",
          "loanable.owner.user.avatar",
          "loanable.owner.user.name",
          "loanable.owner.user.last_name",
          "loanable.owner.user.full_name",
          "loanable.owner.user.phone",
          "loanable.padlock.name",
          "loanable.type",
          "loanable.instructions",
          "loanable.has_padlock",
          "payment.*",
          "pre_payment.*",
          "takeover.*",
          "takeover.image.*",
        ].join(","),
      },
    },
  },
];
