import Login from "../views/Login.vue";
import Password from "../views/Password.vue";
import PasswordRequest from "../views/password/Request.vue";
import PasswordReset from "../views/password/Reset.vue";

export default [
  {
    path: "/login",
    name: "login",
    component: Login,
    meta: {
      title: "titles.login",
    },
  },
  {
    path: "/login/callback",
    name: "login-callback",
    component: Login,
    meta: {
      title: "titles.login",
    },
  },
  {
    path: "/password",
    component: Password,
    meta: {
      title: "titles.password",
    },
    children: [
      {
        path: "request",
        name: "password-request",
        component: PasswordRequest,
        meta: {
          title: "titles.password-request",
        },
      },
      {
        path: "reset",
        name: "password-reset",
        component: PasswordReset,
        meta: {
          title: "titles.password-reset",
        },
      },
    ],
  },
];
