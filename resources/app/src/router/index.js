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
        return to.name === 'community-map' || to.name === 'community-list';
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
          'intention.*',
          'payment.*',
          'pre_payment.*',
          'takeover.*',
          'takeover.image.*',
          'handover.*',
          'handover.image.*',
          'loanable.id',
          'loanable.name',
          'loanable.type',
          'borrower.id',
          'borrower.user.id',
          'borrower.user.name',
          'borrower.user.avatar',
          'loanable.owner.id',
          'loanable.owner.user.id',
          'loanable.owner.user.name',
          'loanable.owner.user.phone',
          'loanable.owner.user.avatar',
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
