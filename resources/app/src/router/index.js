import Vue from 'vue';
import VueRouter from 'vue-router';

import Dashboard from '../views/Dashboard.vue';
import Help from '../views/Help.vue';
import Home from '../views/Home.vue';
import Login from '../views/Login.vue';
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
    path: '/app',
    name: 'dashboard',
    component: Dashboard,
    meta: {
      title: 'Tableau de bord',
    },
  },
//  {
//    path: '/community/map',
//    name: 'map',
//    component: CommunityMap,
//    meta: {
//      title: 'Trouver un véhicule',
//    },
//  },
  {
    path: '/register/map',
    name: 'map',
    component: RegistrationMap,
    meta: {
      data: {
        communities: {
          retrieve: {
            params: {
              fields: 'id,name,description,center,area_google',
            }
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
