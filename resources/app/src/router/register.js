import Register from "@/views/Register.vue";
import RegisterIntro from "@/views/register/Intro.vue";
import RegisterStep from "@/views/register/Step.vue";

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
        path: ":step",
        name: "register-step",
        component: RegisterStep,
        props: true,
        meta: {
          auth: true,
          slug: "users",
          params: {
            fields:
              "*,avatar.*,owner.*,borrower.*.*,communities.id,communities.name,communities.role,communities.proof",
          },
        },
      },
    ],
  },
];
