import Dashboard from '../views/Dashboard.vue';
import Faq from '../views/Faq.vue';
import Help from '../views/Help.vue';
import Home from '../views/Home.vue';
import Login from '../views/Login.vue';

export default [
  {
    path: '/',
    name: 'home',
    component: Home,
    meta: {
      data: {
        stats: {
          retrieve: {},
        },
      },
    },
  },
  {
    path: '/faq',
    name: 'faq',
    component: Faq,
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
    path: '/login/callback',
    name: 'login-callback',
    component: Login,
    meta: {
      title: 'titles.login',
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
    path: '/help',
    name: 'help',
    component: Help,
    meta: {
      title: 'Aide de LocoMotion',
    },
  },
];
