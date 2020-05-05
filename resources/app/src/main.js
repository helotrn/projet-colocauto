import Vue from 'vue';
import { BootstrapVue, BootstrapVueIcons } from 'bootstrap-vue';
import axios from 'axios';
import VueAxios from 'vue-axios';
import * as VueGoogleMaps from 'vue2-google-maps';
import vueHeadful from 'vue-headful';
import VueScrollTo from 'vue-scrollto';
import VueTheMask from 'vue-the-mask';
import * as Sentry from '@sentry/browser';
import * as Integrations from '@sentry/integrations';

import {
  ValidationObserver,
  ValidationProvider,
  extend,
  localize,
} from 'vee-validate';

import fr from 'vee-validate/dist/locale/fr.json';
import * as rules from 'vee-validate/dist/rules';

import App from './App.vue';
import router from './router';
import store from './store';
import i18n from './i18n';

import LayoutHeader from './components/Layout/Header.vue';
import LayoutFooter from './components/Layout/Footer.vue';
import LayoutLoading from './components/Layout/Loading.vue';
import LayoutPage from './components/Layout/Page.vue';

import { filters } from './helpers';
import dayjs from './helpers/dayjs';

import '@/assets/scss/main.scss';
import 'vue-cal/dist/vuecal.css';

if (process.env.NODE_ENV !== 'development') {
  Sentry.init({
    dsn: 'https://d1a14784f15a4d88a021b1ad577a240a@sentry.molotov.ca/34',
    release: process.env.VUE_APP_RELEASE,
    integrations: [
      new Integrations.Vue({ Vue, attachProps: true, logErrors: true }),
    ],
  });
}

Vue.config.productionTip = false;

Vue.use(VueTheMask);
Vue.use(VueScrollTo);
Vue.use(BootstrapVue);
Vue.use(BootstrapVueIcons);
Vue.use(VueGoogleMaps, {
  load: {
    key: process.env.VUE_APP_GOOGLE_MAPS_API_KEY,
    libraries: 'drawing',
  },
});

Vue.component('layout-footer', LayoutFooter);
Vue.component('layout-header', LayoutHeader);
Vue.component('layout-loading', LayoutLoading);
Vue.component('layout-page', LayoutPage);

// Install VeeValidate rules and localization
Object.keys(rules).forEach((rule) => {
  extend(rule, rules[rule]);
});
extend('nullable', () => true); // More of a backend concern, just skip through
extend('date', (v) => {
  const parsedDate = new Date(v);
  return !Number.isNaN(parsedDate.getTime());
});
extend('present', v => v !== null && v !== undefined);
extend('boolean', v => typeof v === 'boolean');
extend('before', (v, args) => {
  switch (args) {
    case 'today': {
      const parsedDate = new Date(v);
      if (!Number.isNaN(parsedDate.getTime())) {
        return parsedDate < new Date();
      }
      return false;
    }
    default:
      return true; // Pass to backend if handling cannot be done in frontend
  }
});

localize('fr', fr);

Vue.component('ValidationObserver', ValidationObserver);
Vue.component('ValidationProvider', ValidationProvider);

Vue.component('vue-headful', vueHeadful);

Object.keys(filters).forEach(f => Vue.filter(f, filters[f]));

axios.defaults.baseURL = `${process.env.VUE_APP_API_URL}/v1`;
axios.interceptors.request.use((config) => {
  if (store.state.token) {
    // eslint-disable-next-line
    config.headers.Authorization = `Bearer ${store.state.token}`;
  }
  return config;
});
Vue.use(VueAxios, axios);

Object.defineProperties(Vue.prototype, {
  $dayjs: {
    get() {
      return dayjs;
    },
  },
});
Vue.dayjs = dayjs;

new Vue({
  i18n,
  render: h => h(App),
  router,
  store,
}).$mount('#app');
