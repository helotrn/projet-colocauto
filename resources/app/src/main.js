import Vue from 'vue';
import BootstrapVue from 'bootstrap-vue';
import axios from 'axios';
import VueAxios from 'vue-axios';

import App from './App.vue';
import router from './router';
import store from './store';

import '@/assets/scss/main.scss';

Vue.config.productionTip = false;

Vue.use(BootstrapVue);

axios.defaults.baseURL = `${process.env.VUE_APP_API_URL}/v1`;
Vue.use(VueAxios, axios);

new Vue({
  router,
  store,
  render: h => h(App),
}).$mount('#app');
