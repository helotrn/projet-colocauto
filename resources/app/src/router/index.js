import Vue from 'vue';
import VueRouter from 'vue-router';

import CommunityMap from '../views/community/Map.vue';
import Dashboard from '../views/Dashboard.vue';
import Help from '../views/Help.vue';
import Home from '../views/Home.vue';
import Login from '../views/Login.vue';
import Register from '../views/Register.vue';
import Admin from '../views/Admin.vue';
import AdminDashboard from '../views/admin/Dashboard.vue';
import AdminCommunities from '../views/admin/Communities.vue';
import AdminCommunity from '../views/admin/Community.vue';


import RegistrationMap from '../views/registration/Map.vue';

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
      title: 'Se connecter',
    },
  },
  {
    path: '/register',
    name: 'register',
    component: Register,
    meta: {
      title: "S'inscrire",
    },
  },
  {
    path: '/app',
    name: 'dashboard',
    component: Dashboard,
    meta: {
      title: 'Tableau de bord',
    },
  },
  {
    path: '/community/map',
    name: 'community-map',
    component: CommunityMap,
    meta: {
      title: 'Trouver un véhicule',
    },
  },
  {
    path: '/register/map',
    name: 'registration-map',
    component: RegistrationMap,
    meta: {
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
    path: '/help',
    name: 'help',
    component: Help,
    meta: {
      title: 'Aide de Locomotion',
    },
  },
  {
    path: '/admin',
    component: Admin,
    children: [
      {
        path: '',
        component: AdminDashboard,
      },
      {
        path: 'communities',
        component: AdminCommunities,
        meta: {
          data: {
            communities: {
              retrieve: {
                fields: 'id,name',
              },
            },
          },
          title: 'Communautés',
        },
      },
      {
        path: 'communities/:id',
        component: AdminCommunity,
      },
    ],
  },
];

const router = new VueRouter({
  mode: 'history',
  base: process.env.BASE_URL,
  routes,
});

router.beforeEach((to, from, next) => {
  if (to.meta && to.meta.title) {
    document.title = `LocoMotion | ${to.meta.title}`;
  } else {
    document.title = 'LocoMotion';
  }

  next();
});

export default router;
