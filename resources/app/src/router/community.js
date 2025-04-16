import Community from "@/views/Community.vue";
import CommunityDashboard from "@/views/community/Dashboard.vue";
import CommunityInfo from "@/views/community/Info.vue";
import ProfileLoanables from "@/views/profile/Loanables.vue";
import ProfileLoans from "@/views/profile/Loans.vue";

export default [
  {
    path: "/community",
    component: Community,
    meta: {
      auth: true,
      titles: "titles.community",
    },
    children: [
      {
        path: "",
        component: CommunityInfo,
        props: { id: 'new' },
        meta: {
          auth: true,
          creatable: true,
          slug: "communities",
          title: "titles.community",
        },
      },
      {
        path: "members",
        component: CommunityDashboard,
        meta: {
          auth: true,
          title: "titles.community",
          data: {
            global: {
              load: {},
            },
            communities: {
              retrieveOne: {
                params: {
                  fields:
                    "id,name," +
                    "approved_users_count," +
                    "users.id,users.full_name,users.avatar,users.description," +
                    "users.owner.id,users.tags.*",
                },
                id({ user }) {
                  if (!user || !user.communities || !user.communities[0]) {
                    return 0;
                  }

                  return user.communities[0].id;
                },
              },
            },
          },
        },
      },
      {
        path: "loanables",
        component: ProfileLoanables,
        meta: {
          auth: true,
          title: "titles.loanables",
          slug: "loanables",
          data: {
            loanables: {
              retrieve: {
                fields: "id,name,type,image.*,community.id,community.name",
              },
            },
          },
        }
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
