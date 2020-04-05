import Vue from 'vue';
import VueRouter from 'vue-router';

import Loan from '../views/Loan.vue';

import adminRoutes from './admin';
import baseRoutes from './base';
import communityRoutes from './community';
import profileRoutes from './profile';
import registerRoutes from './register';

Vue.use(VueRouter);

const routes = [
  ...baseRoutes,
  adminRoutes,
  communityRoutes,
  profileRoutes,
  registerRoutes,
  {
    path: '/loans/:id',
    component: Loan,
    props: true,
    meta: {
      auth: true,
      slug: 'loans',
      skipCleanup(to) {
        return to.name === 'community-view';
      },
      data: {
        loans: {
          options: {},
        },
      },
      params: {
        fields: [
          '*',
          'actions.*',
          'borrower.id',
          'borrower.user.avatar',
          'borrower.user.id',
          'borrower.user.name',
          'extensions.*',
          'handover.*',
          'handover.image.*',
          'incidents.*',
          'intention.*',
          'loanable.id',
          'loanable.name',
          'loanable.owner.id',
          'loanable.owner.user.avatar',
          'loanable.owner.user.id',
          'loanable.owner.user.name',
          'loanable.owner.user.phone',
          'loanable.type',
          'payment.*',
          'pre_payment.*',
          'takeover.*',
          'takeover.image.*',
        ].join(','),
      },
    },
  },
];

const router = new VueRouter({
  mode: 'history',
  base: process.env.BASE_URL,
  routes,
  scrollBehavior(to) {
    if (to.matched.length > 1) {
      return { x: 0, y: 0 };
    }

    return undefined;
  },
});

export default router;
