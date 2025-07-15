import Community from "@/views/Community.vue";
import CommunityMembers from "@/views/community/Members.vue";
import CommunityInfo from "@/views/community/Info.vue";
import CommunityLoanables from "@/views/community/Loanables.vue";
import CommunityLoanable from "@/views/community/Loanable.vue";
import CommunityLoans from "@/views/community/Loans.vue";

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
        path: ":id",
        name: "community-info",
        component: CommunityInfo,
        props: true,
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
        path: ":id/members",
        name: "community-members",
        component: CommunityMembers,
        meta: {
          auth: true,
          title: "titles.community",
          params: {
            fields:
              "id,name," +
              "approved_users_count," +
              "users.*,users.avatar.*," +
              "users.owner.id,invitations," +
              "users.loanables",
          },
        },
      },
      {
        path: ":id/loanables",
        name: "community-loanables",
        component: CommunityLoanables,
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
        path: ":cid/loanables/:id",
        name: "community-single-loanable",
        component: CommunityLoanable,
        props: true,
        meta: {
          auth: true,
          slug: "loanables",
          params: {
            fields:
              "*,events,type,community.id,community.center,community.name,owner.id,owner.user.id,owner.user.full_name," +
              "owner.user.communities.center,owner.user.communities.id,owner.user.avatar,image.*,report.*,balance," +
              "coowners,coowners.user,coowners.user.full_name,coowners.user.avatar,coowners.user.phone,coowners.title," +
              "coowners.receive_notifications,coowners.pays_loan_price,coowners.pays_provisions,coowners.pays_owner," +
              "reports.*, reports.pictures, reports.incident.*, reports.incident.loan",
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
        path: ":id/loans",
        name: "community-loans",
        component: CommunityLoans,
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
                community_id({route}){
                  return route.params.id
                },
              },
            },
          },
        },
      },
    ],
  },
];
