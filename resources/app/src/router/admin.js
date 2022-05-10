import Admin from "../views/Admin.vue";
import AdminCommunities from "../views/admin/Communities.vue";
import AdminCommunity from "../views/admin/Community.vue";
import AdminDashboard from "../views/admin/Dashboard.vue";
import AdminInvoice from "../views/admin/Invoice.vue";
import AdminInvoices from "../views/admin/Invoices.vue";
import AdminLoan from "../views/admin/Loan.vue";
import AdminLoanable from "../views/admin/Loanable.vue";
import AdminLoanables from "../views/admin/Loanables.vue";
import AdminLoans from "../views/admin/Loans.vue";
import AdminPadlock from "../views/admin/Padlock.vue";
import AdminPadlocks from "../views/admin/Padlocks.vue";
import AdminTag from "../views/admin/Tag.vue";
import AdminTags from "../views/admin/Tags.vue";
import AdminUser from "../views/admin/User.vue";
import AdminUsers from "../views/admin/Users.vue";

export default {
  path: "/admin",
  component: Admin,
  meta: {
    auth: true,
    title: "titles.admin",
  },
  children: [
    {
      path: "",
      component: AdminDashboard,
      meta: {
        auth: true,
        title: "titles.dashboard",
      },
    },
    {
      path: "communities",
      component: AdminCommunities,
      meta: {
        auth: true,
        creatable: true,
        slug: "communities",
        data: {
          communities: {
            retrieve: {
              fields: "id,name,type,parent.id,parent.name",
              for: "edit",
            },
          },
        },
        title: "titles.communities",
      },
    },
    {
      path: "communities/:id",
      component: AdminCommunity,
      props: true,
      meta: {
        auth: true,
        slug: "communities",
        params: {
          fields: ["*", "pricings.*", "parent.*"].join(","),
          for: "edit",
        },
        data: {
          users: {
            retrieve: {
              "communities.id": ({
                route: {
                  params: { id },
                },
              }) => id,
              fields: [
                "id",
                "full_name",
                "communities.role",
                "communities.proof",
                "communities.approved_at",
                "communities.suspended_at",
              ].join(","),
              mapResults(item) {
                const communityId = parseInt(this.route.params.id, 10);
                const newItem = {
                  ...item.communities.find((c) => c.id === communityId),
                  ...item,
                };
                return newItem;
              },
              per_page: -1,
            },
          },
        },
      },
    },
    {
      path: "loanables",
      component: AdminLoanables,
      meta: {
        auth: true,
        creatable: true,
        slug: "loanables",
        data: {
          loanables: {
            retrieve: {
              fields: "id,name,type,owner.id,owner.user.full_name,owner.user.id,deleted_at",
            },
          },
        },
        title: "titles.loanables",
      },
    },
    {
      path: "loanables/:id",
      component: AdminLoanable,
      props: true,
      meta: {
        auth: true,
        slug: "loanables",
        params: {
          fields:
            "*,owner.user.full_name,owner.user.communities.name," +
            "owner.user.communities.parent.name,community.name," +
            "community.parent.name,padlock.name,report.*,image.*",
          with_deleted: true,
        },
        title: "titles.loanable",
      },
    },
    {
      path: "users",
      component: AdminUsers,
      meta: {
        auth: true,
        creatable: true,
        slug: "users",
        data: {
          users: {
            retrieve: {
              fields: "id,name,last_name,full_name,email,is_deactivated",
              is_deactivated: 0,
            },
          },
        },
        title: "titles.users",
      },
    },
    {
      path: "users/:id",
      component: AdminUser,
      props: true,
      meta: {
        auth: true,
        slug: "users",
        params: {
          fields:
            "*,owner.*,borrower.*,borrower.gaa.*,borrower.insurance.*,borrower.saaq.*," +
            "loanables.*,loanables.loans.*,avatar.*," +
            "loanables.loans.borrower.user.full_name,communities.*,loans.*," +
            "invoices.*,invoices.total,invoices.total_with_taxes,loans.borrower.user.*," +
            "loans.loanable.name,communities.tags.*,payment_methods.*",
        },
        title: "titles.user",
      },
    },
    {
      path: "invoices",
      component: AdminInvoices,
      meta: {
        auth: true,
        creatable: false,
        slug: "invoices",
        data: {
          invoices: {
            retrieve: {
              fields: "*,user.id,user.full_name",
            },
          },
        },
        title: "titles.invoices",
      },
    },
    {
      path: "invoices/:id",
      component: AdminInvoice,
      props: true,
      meta: {
        auth: true,
        slug: "invoices",
        params: {
          fields: "*,bill_items.*,user.*",
        },
        title: "titles.invoice",
        data: {
          users: {
            retrieveOne: {
              conditional({ route }) {
                return !!route && !!route.query && !!route.query.user_id;
              },
              params: {
                fields: "full_name,address,postal_code",
              },
              id({ route: { query } }) {
                return query.user_id;
              },
            },
          },
        },
      },
    },
    {
      path: "loans",
      component: AdminLoans,
      meta: {
        auth: true,
        creatable: true,
        slug: "loans",
        data: {
          loans: {
            retrieve: {
              fields: "*,borrower.user.full_name,loanable.owner.user.full_name,community.name",
            },
          },
        },
        title: "titles.loans",
      },
    },
    {
      path: "loans/:id",
      component: AdminLoan,
      props: true,
      meta: {
        auth: true,
        slug: "loans",
        params: {
          fields: [
            "*",
            "total_estimated_cost",
            "actions.*",
            "borrower.id",
            "borrower.user.avatar",
            "borrower.user.name",
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
            "loanable.owner.user.full_name",
            "loanable.owner.user.phone",
            "loanable.padlock.name",
            "loanable.type",
            "loanable.has_padlock",
            "payment.*",
            "pre_payment.*",
            "takeover.*",
            "takeover.image.*",
          ].join(","),
        },
        title: "titles.loan",
      },
    },
    {
      path: "padlocks",
      component: AdminPadlocks,
      meta: {
        auth: true,
        creatable: false,
        slug: "padlocks",
        data: {
          padlocks: {
            retrieve: {
              fields: "*,loanable.name",
            },
          },
        },
        title: "titles.padlocks",
      },
    },
    {
      path: "padlocks/:id",
      component: AdminPadlock,
      props: true,
      meta: {
        auth: true,
        slug: "padlocks",
        params: {
          fields: "*,loanable.name",
        },
        title: "titles.loan",
      },
    },
    {
      path: "tags",
      component: AdminTags,
      meta: {
        auth: true,
        creatable: true,
        slug: "tags",
        data: {
          tags: {
            retrieve: {
              fields: "*",
            },
          },
        },
        title: "titles.tags",
      },
    },
    {
      path: "tags/:id",
      component: AdminTag,
      props: true,
      meta: {
        auth: true,
        slug: "tags",
        params: {
          fields: "*",
        },
        title: "titles.tag",
      },
    },
  ],
};
