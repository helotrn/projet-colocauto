import Vue from 'vue';
import VueRouter from 'vue-router';

import Dashboard from '../views/Dashboard.vue';
import Help from '../views/Help.vue';
import Home from '../views/Home.vue';
import Login from '../views/Login.vue';

import Community from '../views/Community.vue';
import CommunityDashboard from '../views/community/Dashboard.vue';
import CommunityList from '../views/community/List.vue';
import CommunityMap from '../views/community/Map.vue';

import adminRoutes from './admin';
import profileRoutes from './profile';
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
    path: '/community',
    component: Community,
    meta: {
      auth: true,
      titles: 'titles.community',
    },
    children: [
      {
        path: '',
        component: CommunityDashboard,
        meta: {
          auth: true,
          title: 'titles.community',
          data: {
            communities: {
              retrieveOne: {
                params: {
                  fields: 'id,name,users.id,users.full_name,users.avatar,users.description,'
                   + 'users.owner.id',
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
        path: 'map',
        component: CommunityMap,
        meta: {
          auth: true,
          title: 'titles.find_vehicle',
          data: {
            loanables: {
              retrieve: {
                fields: 'id,type,name,position,owner.user.id,owner.user.full_name,owner.user.avatar,image.*',
              },
            },
          },
        },
      },
      {
        path: 'list',
        component: CommunityList,
        meta: {
          auth: true,
          title: 'titles.find_vehicle',
          slug: 'loanables',
          data: {
            loanables: {
              retrieve: {
                fields: 'id,type,name,position,available,owner.user.id,owner.user.full_name,owner.user.avatar,image.*',
              },
            },
            loans: {
              loadEmpty: {},
            },
          },
        },
      },
    ],
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
    path: '/help',
    name: 'help',
    component: Help,
    meta: {
      title: 'Aide de Locomotion',
    },
  },
  adminRoutes,
  profileRoutes,
  registerRoutes,
];

const router = new VueRouter({
  mode: 'history',
  base: process.env.BASE_URL,
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (to.matched.length > 1) {
      return { x: 0, y: 0 }
    }

    return undefined;
  },
});

export default router;
