import Vue from 'vue';
import BootstrapVue from 'bootstrap-vue';
import axios from 'axios';
import VueAxios from 'vue-axios';

import App from './App.vue';
import router from './router';
import store from './store';

import LayoutHeader from './components/Layout/Header.vue';
import LayoutFooter from './components/Layout/Footer.vue';

import '@/assets/scss/main.scss';

Vue.config.productionTip = false;

Vue.use(BootstrapVue);

Vue.component('layout-footer', LayoutFooter);
Vue.component('layout-header', LayoutHeader);

axios.defaults.baseURL = `${process.env.VUE_APP_API_URL}/v1`;
axios.interceptors.request.use((config) => {
  if (store.state.token) {
    // eslint-disable-next-line
    config.headers.Authorization = `Bearer ${store.state.token}`;
  }
  return config;
});
Vue.use(VueAxios, axios);

new Vue({
  router,
  store,
  render: h => h(App),
}).$mount('#app');
