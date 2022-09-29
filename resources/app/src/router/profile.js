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
            fields: "id,name,email,accept_conditions",
          },
        },
      },
      {
        path: "locomotion",
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
        path: "communities",
        component: ProfileCommunities,
        meta: {
          auth: true,
          title: "titles.communities",
          slug: "users",
          params: {
            fields: "id,communities.id,communities.name,communities.requirements,communities.proof",
          },
        },
      },
      {
        path: "invoices",
        component: ProfileInvoices,
        meta: {
          auth: true,
          title: "titles.invoices",
          slug: "invoices",
          data: {
            invoices: {
              retrieve: {
                fields: "*",
              },
            },
          },
        },
      },
      {
        path: "invoices/:id",
        component: ProfileInvoice,
        props: true,
        meta: {
          auth: true,
          title: "titles.invoice",
          slug: "invoices",
          params: {
            fields: "*,bill_items.*,user.*",
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
        path: "loanables/:id",
        component: ProfileLoanable,
        props: true,
        meta: {
          auth: true,
          slug: "loanables",
          params: {
            fields:
              "*,events,type,community.id,community.center,owner.id,owner.user.id," +
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
      {
        path: "locomotion",
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
        path: "payment_methods",
        component: ProfilePaymentMethods,
        meta: {
          auth: true,
          creatable: true,
          title: "titles.payment_methods",
          slug: "paymentMethods",
          data: {
            paymentMethods: {
              retrieve: {
                fields: "*",
              },
            },
          },
        },
      },
      {
        path: "payment_methods/:id",
        component: ProfilePaymentMethod,
        props: true,
        meta: {
          auth: true,
          slug: "paymentMethods",
          params: {
            fields: "*",
          },
          title: "titles.payment_method",
        },
      },
    ],
  },
];
