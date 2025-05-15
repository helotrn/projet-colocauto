import Profile from "@/views/Profile.vue";
import ProfileAccount from "@/views/profile/Account.vue";
import CommunityLoanables from "@/views/community/Loanables.vue";
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
        component: CommunityLoanables,
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
        redirect: "/community/loanables/:id"
      },
      {
        path: "loans",
        redirect: "/community/loans"
      },
    ],
  },
];
