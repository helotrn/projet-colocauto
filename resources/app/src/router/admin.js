import Admin from "@/views/Admin.vue";
import AdminCommunities from "@/views/admin/Communities.vue";
import AdminCommunity from "@/views/admin/Community.vue";
import AdminDashboard from "@/views/admin/Dashboard.vue";
import AdminInvoice from "@/views/admin/Invoice.vue";
import AdminInvoices from "@/views/admin/Invoices.vue";
import AdminLoan from "@/views/admin/Loan.vue";
import AdminLoanable from "@/views/admin/Loanable.vue";
import AdminLoanables from "@/views/admin/Loanables.vue";
import AdminLoans from "@/views/admin/Loans.vue";
import AdminPadlock from "@/views/admin/Padlock.vue";
import AdminPadlocks from "@/views/admin/Padlocks.vue";
import AdminTag from "@/views/admin/Tag.vue";
import AdminTags from "@/views/admin/Tags.vue";
import AdminUser from "@/views/admin/User.vue";
import AdminUsers from "@/views/admin/Users.vue";
import AdminInvitations from "@/views/admin/Invitations.vue";
import AdminInvitation from "@/views/admin/Invitation.vue";
import AdminExpenses from "@/views/admin/Expenses.vue";
import AdminExpense from "@/views/admin/Expense.vue";
import AdminExpenseTags from "@/views/admin/ExpenseTags.vue";
import AdminExpenseTag from "@/views/admin/ExpenseTag.vue";
import AdminRefunds from "@/views/admin/Refunds.vue";
import AdminRefund from "@/views/admin/Refund.vue";

export default [
  {
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
                fields: "id,name,type,parent.id,parent.name,admins.id,admins.full_name",
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
            fields: ["*", "pricings.*", "parent.*", "invitations.*"].join(","),
            for: "edit",
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
                fields: "id,name,type,owner.id,owner.user.full_name,owner.user.id,community.id,community.name,deleted_at",
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
              "*,owner.user.full_name,owner.user.communities.name,owner.user.avatar," +
              "owner.user.communities.parent.name,community.name," +
              "coowners.user,coowners.user.full_name,coowners.user.phone,coowners.user.avatar,coowners.title,coowners.receive_notifications," +
              "community.parent.name,padlock.name,report.*,image.*,balance",
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
                fields: "id,name,last_name,full_name,email,deleted_at,communities.id,communities.name,role,administrable_communities",
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
            with_deleted: true,
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
                fields:
                  "*,borrower.user.full_name,loanable.name,loanable.owner.user.full_name,community.name,loanable.is_self_service,incidents.status,intention.status,pre_payment.status,takeover.status,extensions.status,handover.status,payment.status,",
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
              "final_distance",
              "actions.*",
              "borrower.id",
              "borrower.user.avatar",
              "borrower.user.email",
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
              "loanable.position",
              "loanable.type",
              "loanable.comments",
              "loanable.image",
              "loanable.instructions",
              "loanable.has_padlock",
              "loanable.location_description",
              "loanable.is_self_service",
              "bike.model",
              "bike.bike_type",
              "bike.size",
              "car.brand",
              "car.model",
              "car.year_of_circulation",
              "car.transmission_mode",
              "car.engine",
              "car.papers_location",
              "trailer.maximum_charge",
              "trailer.dimensions",
              "payment.*",
              "pre_payment.*",
              "takeover.*",
              "takeover.image.*",
              "expenses.id",
              "expenses.amount",
              "expenses.tag",
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
      {
        path: "invitations",
        component: AdminInvitations,
        meta: {
          auth: true,
          creatable: true,
          slug: "invitations",
          data: {
            invitations: {
              retrieve: {
                fields: "*,community.name",
              },
            },
          },
          title: "titles.invitations",
        },
      },
      {
        path: "invitations/:id",
        component: AdminInvitation,
        props: true,
        meta: {
          auth: true,
          slug: "invitations",
          params: {
            fields: "*",
          },
          title: "titles.invitation",
        },
      },
      {
        path: "expenses",
        component: AdminExpenses,
        meta: {
          auth: true,
          creatable: true,
          slug: "expenses",
          data: {
            expenses: {
              retrieve: {
                fields: "*,loanable.name,user.full_name,tag.name,tag.color,changes",
                for: "edit",
              },
            },
          },
          title: "titles.expenses",
        },
      },
      {
        path: "expenses/:id",
        component: AdminExpense,
        props: true,
        meta: {
          auth: true,
          slug: "expenses",
          params: {
            fields: "*, changes, changes.user,changes.description,changes.created_at",
          },
          title: "titles.expense",
        },
      },
      {
        path: "expense_tags",
        component: AdminExpenseTags,
        meta: {
          auth: true,
          creatable: true,
          slug: "expense_tags",
          data: {
            expense_tags: {
              retrieve: {
                fields: "*",
                for: "edit",
              },
            },
          },
          title: "titles.expense_tags",
        },
      },
      {
        path: "expense_tags/:id",
        component: AdminExpenseTag,
        props: true,
        meta: {
          auth: true,
          slug: "expense_tags",
          params: {
            fields: "*",
          },
          title: "titles.expense_tag",
        },
      },
      {
        path: "refunds",
        component: AdminRefunds,
        meta: {
          auth: true,
          creatable: true,
          slug: "refunds",
          data: {
            refunds: {
              retrieve: {
                fields: "*,user.full_name,credited_user.full_name,changes",
                for: "edit",
              },
            },
          },
          title: "titles.refunds",
        },
      },
      {
        path: "refunds/:id",
        component: AdminRefund,
        props: true,
        meta: {
          auth: true,
          slug: "refunds",
          params: {
            fields: "*, changes, changes.user,changes.description,changes.created_at",
          },
          title: "titles.refund",
        },
      },
    ],
  },
];
