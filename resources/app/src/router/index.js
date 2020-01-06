import Vue from 'vue';
import VueRouter from 'vue-router';
import Home from '../views/Home.vue';
import Login from '../views/Login.vue';
import Dashboard from '../views/Dashboard.vue';

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
      auth: false,
      title: 'Se connecter',
    },
  },
  {
    path: '/app',
    name: 'dashboard',
    component: Dashboard,
    meta: {
      auth: false,
      title: 'Tableau de bord',
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
