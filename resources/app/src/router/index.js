import Vue from "vue";
import VueRouter from "vue-router";

import Loan from "@/views/Loan.vue";
import NotFound from "@/views/NotFound.vue";
import CommunityOverview from "@/views/community/Overview.vue";

import adminRoutes from "@/router/admin";
import authRoutes from "@/router/auth";
import baseRoutes from "@/router/base";
import communityRoutes from "@/router/community";
import loanRoutes from "@/router/loans";
import profileRoutes from "@/router/profile";
import registerRoutes from "@/router/register";

Vue.use(VueRouter);

const routes = [
  ...adminRoutes,
  ...authRoutes,
  ...baseRoutes,
  ...communityRoutes,
  ...loanRoutes,
  ...profileRoutes,
  ...registerRoutes,
  {
    path: "/404",
    component: NotFound,
    meta: {},
  },
  { path: "*", redirect: "/404" },
];

const router = new VueRouter({
  mode: "history",
  base: process.env.BASE_URL,
  routes,
  scrollBehavior(to) {
    if (to.matched.length >= 1) {
      return { x: 0, y: 0 };
    }

    return undefined;
  },
});

router.beforeEach((to, from, next) => {
  const {
    body: { style },
  } = document;

  style.overflow = "auto";
  style.height = "auto";

  next();
});

export default router;
