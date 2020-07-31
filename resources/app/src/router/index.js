import Vue from 'vue';
import VueRouter from 'vue-router';

import Loan from '@/views/Loan.vue';
import CommunityOverview from '@/views/community/Overview.vue';

import adminRoutes from './admin';
import authRoutes from './auth';
import baseRoutes from './base';
import communityRoutes from './community';
import profileRoutes from './profile';
import registerRoutes from './register';

Vue.use(VueRouter);

const routes = [
  ...authRoutes,
  ...baseRoutes,
  adminRoutes,
  communityRoutes,
  profileRoutes,
  registerRoutes,
  {
    path: '/communities',
    name: 'communities-overview',
    component: CommunityOverview,
    meta: {
      slug: 'communities',
      skipCleanup: true,
      data: {
        stats: {
          retrieve: {
            type: 'neighborhood',
          },
        },
      },
      title: 'titles.communities-overview',
    },
  },
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
          'total_estimated_cost',
          'actions.*',
          'borrower.id',
          'borrower.user.avatar',
          'borrower.user.name',
          'borrower.user.full_name',
          'borrower.user.phone',
          'extensions.*',
          'handover.*',
          'handover.image.*',
          'handover.expense.*',
          'incidents.*',
          'intention.*',
          'loanable.name',
          'loanable.community.name',
          'loanable.owner.user.avatar',
          'loanable.owner.user.name',
          'loanable.owner.user.full_name',
          'loanable.owner.user.phone',
          'loanable.padlock.name',
          'loanable.type',
          'loanable.has_padlock',
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
    if (to.matched.length >= 1) {
      return { x: 0, y: 0 };
    }

    return undefined;
  },
});

router.beforeEach((to, from, next) => {
  const { body: { style } } = document;

  style.overflow = 'auto';
  style.height = 'auto';

  next();
});

export default router;
