import Vue from 'vue';
import { BootstrapVue, BootstrapVueIcons } from 'bootstrap-vue';
import axios from 'axios';
import VueAxios from 'vue-axios';
import * as VueGoogleMaps from 'vue2-google-maps';
import vueHeadful from 'vue-headful';
import VueScrollTo from 'vue-scrollto';

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
import LayoutPage from './components/Layout/Page.vue';

import { filters } from './helpers';
import dayjs from './helpers/dayjs';

import '@/assets/scss/main.scss';
import 'vue-cal/dist/vuecal.css';

Vue.config.productionTip = false;

Vue.use(VueScrollTo)

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
Vue.component('layout-page', LayoutPage);

// Install VeeValidate rules and localization
Object.keys(rules).forEach((rule) => {
  extend(rule, rules[rule]);
});
extend('present', v => v !== null && v !== undefined);
extend('boolean', v => typeof v === 'boolean');

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
