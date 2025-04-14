import Profile from "@/views/Profile.vue";
import ProfileAccount from "@/views/profile/Account.vue";
import ProfileLoanables from "@/views/profile/Loanables.vue";
import ProfileLoanable from "@/views/profile/Loanable.vue";
import ProfileLocomotion from "@/views/profile/Locomotion.vue";

export default [
  {
    path: "/profile",
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
        redirect: "/profile"
      },
      {
        path: "",
        name: "profile",
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
                fields: "id,name,type,image.*,community.id,community.name",
                "for": "coowned",
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
        name: "single-loanable",
        component: ProfileLoanable,
        props: true,
        meta: {
          auth: true,
          slug: "loanables",
          params: {
            fields:
              "*,events,type,community.id,community.center,community.name,owner.id,owner.user.id,owner.user.full_name," +
              "owner.user.communities.center,owner.user.communities.id,owner.user.avatar,image.*,report.*,balance," +
              "coowners,coowners.user,coowners.user.full_name,coowners.user.avatar,coowners.user.phone,coowners.title," +
              "coowners.receive_notifications,coowners.pays_loan_price,coowners.pays_provisions,coowners.pays_owner"
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
        redirect: "/community/loans"
      },
    ],
  },
];
