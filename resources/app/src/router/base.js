import Dashboard from '../views/Dashboard.vue';
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
    path: '/login',
    name: 'login',
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
      title: 'Aide de Locomotion',
    },
  },
];
