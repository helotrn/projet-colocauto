import Register from "@/views/Register.vue";
import RegisterIntro from "@/views/register/Intro.vue";
import RegisterProfile from "@/views/register/Profile.vue";
import RegisterCommunity from "@/views/register/Community.vue";
import RegisterInvitation from "@/views/register/Invitation.vue";
import RegisterLoanable from "@/views/register/Loanable.vue";

export default [
  {
    path: "/register",
    name: "register",
    component: Register,
    meta: {
      title: "titles.register",
    },
    children: [
      {
        path: "1",
        name: "register-intro",
        component: RegisterIntro,
        meta: {
          title: "titles.register",
        },
      },
      {
        path: "2",
        name: "register-profile",
        component: RegisterProfile,
        meta: {
          auth: true,
          slug: "users",
          params: {
            fields:
              "*,avatar.*,owner.*,borrower.*.*,communities.id,communities.name,communities.role,communities.proof",
          },
        },
      },
      {
        path: "4",
        name: "register-community",
        component: RegisterCommunity,
        props: true,
        meta: {
          auth: true,
          slug: "communities",
          creatable: true,
          params: {
            fields:
              "id,name,invitations",
          },
        },
      },
      {
        path: "4bis",
        name: "invitation-to-community",
        component: RegisterInvitation,
        meta: {
          auth: true,
        },
      },
      {
        path: "5",
        name: "register-loanable",
        component: RegisterLoanable,
        props: true,
        meta: {
          auth: true,
          slug: "loanables",
          creatable: true,
          params: {
            fields:
              "*,owner,reports,report.*,events,coowners,coowners.*",
          },
        },
      },
    ],
  },
];
