import Vue from 'vue';
import VueRouter from 'vue-router';

import Dashboard from '../views/Dashboard.vue';
import Help from '../views/Help.vue';
import Home from '../views/Home.vue';
import Login from '../views/Login.vue';

import CommunityMap from '../views/community/Map.vue';

import Register from '../views/Register.vue';
import RegistrationMap from '../views/registration/Map.vue';

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
    path: '/register',
    name: 'register',
    component: Register,
    props: true,
    meta: {
      title: "S'inscrire",
    },
    children: [
      {
        path: 'map',
        name: 'register-map',
        component: RegistrationMap,
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
        component: Register,
        props: true,
        meta: {
          title: "S'inscrire",
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
      title: 'tableau de bord',
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
