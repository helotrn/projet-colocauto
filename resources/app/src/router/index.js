import Vue from 'vue';
import VueRouter from 'vue-router';

import Dashboard from '../views/Dashboard.vue';
import Help from '../views/Help.vue';
import Home from '../views/Home.vue';
import Login from '../views/Login.vue';

import CommunityMap from '../views/community/Map.vue';

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
    ]
  },
  {
    path: '/app',
    name: 'dashboard',
    component: Dashboard,
    meta: {
      auth: true,
      title: 'Tableau de bord',
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
  {
    path: '/admin',
    component: Admin,
    meta: {
      auth: true,
    },
    children: [
      {
        path: '',
        component: AdminDashboard,
        meta: {
          auth: true,
        },
      },
      {
        path: 'communities',
        component: AdminCommunities,
        meta: {
          auth: true,
          creatable: true,
          slug: 'communities',
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
        props: true,
        meta: {
          auth: true,
          slug: 'communities',
          params: {
            fields: '*,users.*',
          },
          form: {
            id: {
              type: 'number',
              disabled: true,
              required: true,
              label: 'ID',
            },
            name: {
              type: 'text',
              required: true,
              label: 'Nom',
            },
            description: {
              type: 'textarea',
              required: true,
              label: 'Description',
            },
            type: {
              type: 'select',
              label: 'Type',
              options: [
                {
                  text: 'Privée',
                  value: 'private',
                },
                {
                  text: 'Voisinage',
                  value: 'neighborhood',
                },
                {
                  text: 'Quartier',
                  value: 'borough',
                },
              ],
            },
          },
        },
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
