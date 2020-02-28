import Vue from 'vue';
import VueRouter from 'vue-router';

import Dashboard from '../views/Dashboard.vue';
import Help from '../views/Help.vue';
import Home from '../views/Home.vue';
import Login from '../views/Login.vue';

import Profile from '../views/Profile.vue';
import ProfileAccount from '../views/profile/Account.vue';
import ProfileCommunities from '../views/profile/Communities.vue';
import ProfileLoanables from '../views/profile/Loanables.vue';
import ProfileLoanable from '../views/profile/Loanable.vue';

import Community from '../views/Community.vue';
import CommunityMap from '../views/community/Map.vue';

import adminRoutes from './admin';
import registerRoutes from './register';

Vue.use(VueRouter);

const routes = [
  {
    path: '/',
    name: 'home',
    component: Home,
  },
  {
    path: '/login',
    name: 'login',
    component: Login,
    meta: {
      title: 'titles.login',
    },
  },
  {
    path: '/profile',
    name: 'profile',
    component: Profile,
    meta: {
      title: 'titles.profile',
    },
    children: [
      {
        path: 'account',
        name: 'account',
        component: ProfileAccount,
        meta: {
          title: 'titles.account',
          slug: 'users',
          params: {
            fields: '*,avatar.*,borrower.*.*',
          },
        },
      },
      {
        path: 'communities',
        name: 'communities',
        component: ProfileCommunities,
        meta: {
          title: 'titles.communities',
          slug: 'users',
          params: {
            fields: 'id,communities.*.*',
          },
        },
      },
      {
        path: 'loanables',
        name: 'loanables',
        component: ProfileLoanables,
        meta: {
          creatable: true,
          title: 'titles.loanables',
          slug: 'loanables',
          data: {
            loanables: {
              retrieve: {
                fields: 'id,name,type',
                'owner.user.id': 'me',
              },
            },
          },
        },
      },
      {
        path: 'loanables/:id',
        component: ProfileLoanable,
        props: true,
        meta: {
          auth: true,
          slug: 'loanables',
          params: {
            fields: '*,type',
          },
          title: 'titles.loanable',
        },
      },
    ],
  },
  {
    path: '/community',
    name: 'community',
    component: Community,
    meta: {
      auth: true,
      title: 'titles.community',
      data: {
        communities: {
          retrieveOne: {
            params: {
              fields: 'id,name,users',
            },
            id({ user }) {
              return user.communities[0].id;
            },
          },
        },
      },
    },
  },
  {
    path: '/app',
    name: 'dashboard',
    component: Dashboard,
    meta: {
      auth: true,
      title: 'titles.dashboard',
    },
  },

  {
    path: '/community/map',
    name: 'community-map',
    component: CommunityMap,
    meta: {
      auth: true,
      title: 'Trouver un véhicule',
    },
  },
  {
    path: '/help',
    name: 'help',
    component: Help,
    meta: {
      title: 'Aide de Locomotion',
    },
  },
  adminRoutes,
  registerRoutes,
];

const router = new VueRouter({
  mode: 'history',
  base: process.env.BASE_URL,
  routes,
});

export default router;
