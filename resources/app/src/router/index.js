import Vue from 'vue';
import VueRouter from 'vue-router';

import Dashboard from '../views/Dashboard.vue';
import Help from '../views/Help.vue';
import Home from '../views/Home.vue';
import Login from '../views/Login.vue';

import Profile from '../views/Profile.vue';
import ProfileAccount from '../views/profile/Account.vue';

import Community from '../views/Community.vue';
import CommunityMap from '../views/community/Map.vue';

import Register from '../views/Register.vue';
import RegisterIntro from '../views/register/Intro.vue';
import RegisterStep from '../views/register/Step.vue';
import RegisterMap from '../views/register/Map.vue';

import adminRoutes from './admin';

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
            fields: '*,avatar.*,owner.*,borrower.*.*,communities.id,communities.name,communities.role,payments.*,loanables.*',
          },
        },
      },
      //      {
      //        path: 'payments',
      //        name: 'payments',
      //        component: ProfilePayments,
      //        title: 'titles.profile.payments',
      //      },
      //      {
      //        path: 'reservations',
      //        name: 'reservations',
      //        component: ProfileReservations,
      //        title: 'titles.profile.reservations',
      //      },
      //      {
      //        path: 'vehicles',
      //        name: 'vehicles',
      //        component: ProfileVehicles,
      //        title: 'titles.profile.vehicles',
      //      },
    ],
  },
  {
    path: '/register',
    name: 'register',
    component: Register,
    meta: {
      title: 'titles.register',
    },
    children: [
      {
        path: '1',
        name: 'register-intro',
        component: RegisterIntro,
      },
      {
        path: 'map',
        name: 'register-map',
        component: RegisterMap,
        meta: {
          auth: true,
          data: {
            communities: {
              retrieve: {
                fields: 'id,name,description,center,area_google,center_google',
              },
            },
          },
          title: 'Trouver une communauté',
        },
      },
      {
        path: ':step',
        name: 'register-step',
        component: RegisterStep,
        props: true,
        meta: {
          slug: 'users',
          params: {
            fields: '*,avatar.*,owner.*,borrower.*.*,communities.id,communities.name,communities.role,communities.proof',
          },
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
];

const router = new VueRouter({
  mode: 'history',
  base: process.env.BASE_URL,
  routes,
});

export default router;
