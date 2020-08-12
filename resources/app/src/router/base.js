import Dashboard from '../views/Dashboard.vue';
import Faq from '../views/Faq.vue';
import Help from '../views/Help.vue';
import Home from '../views/Home.vue';
import Insurance from '../views/Insurance.vue';
import Privacy from '../views/Privacy.vue';

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
    path: '/privacy',
    name: 'privacy',
    component: Privacy,
    meta: {
      title: 'titles.privacy',
    },
  },
  {
    path: '/conditions',
    name: 'conditions',
    component: Privacy,
    meta: {
      title: 'titles.privacy',
    },
  },
  {
    path: '/assurances-desjardins',
    name: 'insurance',
    component: Insurance,
    meta: {
      title: 'titles.insurance',
    },
  },
  {
    path: '/faq',
    name: 'faq',
    component: Faq,
    meta: {
      title: 'titles.faq',
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
