import Profile from "@/views/Profile.vue";
import ProfileAccount from "@/views/profile/Account.vue";
import ProfileBorrower from "@/views/profile/Borrower.vue";
import ProfileCommunities from "@/views/profile/Communities.vue";
import ProfileInvoice from "@/views/profile/Invoice.vue";
import ProfileInvoices from "@/views/profile/Invoices.vue";
import ProfileLoans from "@/views/profile/Loans.vue";
import ProfileLoanables from "@/views/profile/Loanables.vue";
import ProfileLoanable from "@/views/profile/Loanable.vue";
import ProfileLocomotion from "@/views/profile/Locomotion.vue";
import ProfilePaymentMethods from "@/views/profile/PaymentMethods.vue";
import ProfilePaymentMethod from "@/views/profile/PaymentMethod.vue";

export default [
  {
    path: "/profile",
    name: "profile",
    component: Profile,
    meta: {
      auth: true,
      title: "titles.profile",
    },
    children: [
      {
        path: "account",
        component: ProfileAccount,
        meta: {
          auth: true,
          title: "titles.account",
          slug: "users",
          params: {
            fields: "id,name,email,accept_conditions,gdpr,newsletter",
          },
        },
      },
      {
        path: "colocauto",
        component: ProfileLocomotion,
        meta: {
          auth: true,
          title: "titles.profile",
          slug: "users",
          params: {
            fields: "*,avatar.*",
          },
        },
      },
      {
        path: "borrower",
        component: ProfileBorrower,
        meta: {
          auth: true,
          title: "titles.borrower",
          slug: "users",
          params: {
            fields: "id,borrower.*,borrower.gaa.*,borrower.saaq.*,borrower.insurance.*",
          },
        },
      },
      {
        path: "loanables",
        component: ProfileLoanables,
        meta: {
          auth: true,
          creatable: true,
          title: "titles.loanables",
          slug: "loanables",
          data: {
            loanables: {
              retrieve: {
                fields: "id,name,type,image.*",
                "owner.user.id": "me",
              },
            },
          },
        },
      },
      {
        path: "loanables/new",
        redirect: "/404"
      },
      {
        path: "loanables/:id",
        component: ProfileLoanable,
        props: true,
        meta: {
          auth: true,
          slug: "loanables",
          params: {
            fields:
              "*,events,type,community.id,community.center,community.name,owner.id,owner.user.id,owner.user.full_name," +
              "owner.user.communities.center,image.*,report.*",
          },
          title: "titles.loanable",
          data: {
            communities: {
              retrieve: {
                fields: "id,name,center",
              },
            },
          },
        },
      },
      {
        path: "loans",
        component: ProfileLoans,
        meta: {
          auth: true,
          title: "titles.loans",
          slug: "loans",
          data: {
            loans: {
              retrieve: {
                fields: [
                  "*",
                  "actions.*",
                  "incidents.*",
                  "extensions.*",
                  "borrower.id",
                  "borrower.user.avatar",
                  "borrower.user.full_name",
                  "borrower.user.id",
                  "loanable.id",
                  "loanable.image.*",
                  "loanable.name",
                  "loanable.owner.id",
                  "loanable.owner.user.avatar.*",
                  "loanable.owner.user.full_name",
                  "loanable.owner.user.id",
                  "loanable.type",
                  "loanable.is_self_service",
                  "incidents.status",
                  "intention.status",
                  "pre_payment.status",
                  "takeover.status",
                  "extensions.status",
                  "handover.status",
                  "payment.status",
                ].join(","),
              },
            },
          },
        },
      },
    ],
  },
];
