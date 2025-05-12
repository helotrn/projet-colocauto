import Community from "@/views/Community.vue";
import CommunityMembers from "@/views/community/Members.vue";
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
        name: "community-info",
        component: CommunityInfo,
        props: { id: 'new' },
        meta: {
          auth: true,
          creatable: true,
          slug: "communities",
          title: "titles.community",
          params: {
            fields:
              "id,name,invitations",
          },
        },
      },
      {
        path: "members",
        component: CommunityMembers,
        meta: {
          auth: true,
          title: "titles.community",
          data: {
            communities: {
              retrieveOne: {
                params: {
                  fields:
                    "id,name," +
                    "approved_users_count," +
                    "users.*,users.avatar.*," +
                    "users.owner.id,invitations",
                },
                id({ user }) {
                  if (!user || !user.main_community || !user.main_community.id) {
                    return 0;
                  }

                  return user.main_community.id;
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
