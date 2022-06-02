import Vue from "vue";
import { BootstrapVue, BootstrapVueIcons } from "bootstrap-vue";
import axios from "axios";
import VueAxios from "vue-axios";
import * as VueGoogleMaps from "vue2-google-maps";
import vueHeadful from "vue-headful";
import VueScrollTo from "vue-scrollto";
import VueTheMask from "vue-the-mask";
import * as Sentry from "@sentry/browser";
import * as Integrations from "@sentry/integrations";

import { ValidationObserver, ValidationProvider, extend, localize } from "vee-validate";
import { strtotime } from "locutus/php/datetime";

import fr from "vee-validate/dist/locale/fr.json";
import * as rules from "vee-validate/dist/rules";

import App from "./App.vue";
import router from "./router";
import store from "./store";
import i18n from "./i18n";

import LayoutHeader from "./components/Layout/Header.vue";
import LayoutFooter from "./components/Layout/Footer.vue";
import LayoutLoading from "./components/Layout/Loading.vue";
import LayoutPage from "./components/Layout/Page.vue";

import { filters } from "./helpers";
import dayjs from "./helpers/dayjs";

import "@/assets/scss/main.scss";
import "vue-cal/dist/vuecal.css";

if (process.env.NODE_ENV !== "development") {
  Sentry.init({
    dsn: "https://d1a14784f15a4d88a021b1ad577a240a@sentry.molotov.ca/34",
    release: process.env.VUE_APP_RELEASE,
    integrations: [new Integrations.Vue({ Vue, attachProps: true, logErrors: true })],
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
    region: "CA",
    libraries: "drawing,places",
    installComponents: true,
  },
});

Vue.component("layout-footer", LayoutFooter);
Vue.component("layout-header", LayoutHeader);
Vue.component("layout-loading", LayoutLoading);
Vue.component("layout-page", LayoutPage);

// Install VeeValidate rules and localization
Object.keys(rules).forEach((rule) => {
  extend(rule, rules[rule]);
});
extend("nullable", () => true); // More of a backend concern, just skip through
extend("date", (v) => {
  const parsedDate = new Date(v);
  return !Number.isNaN(parsedDate.getTime());
});
extend("present", (v) => v !== null && v !== undefined);
extend("boolean", (v) => typeof v === "boolean");
extend("accepted", {
  validate: (v) => v === true,
  message: "Vous devez accepter la condition.",
});
extend("before", {
  validate: (v, args) => {
    const parsedDate = new Date(strtotime(args[0]) * 1000);

    if (!Number.isNaN(parsedDate.getTime())) {
      return new Date(v) <= parsedDate;
    }

    return false;
  },
  message: (field, args) => {
    const parsedDate = new Date(strtotime(args[0]) * 1000);
    return `Le champ ${field} devrait être avant le ${dayjs(parsedDate).format("DD MMMM YYYY")}.`;
  },
});
extend("after", {
  validate: (v, args) => {
    const parsedDate = new Date(strtotime(args[0]) * 1000);

    if (!Number.isNaN(parsedDate.getTime())) {
      return new Date(v) >= parsedDate;
    }

    return false;
  },
  message: (field, args) => {
    const parsedDate = new Date(strtotime(args[0]) * 1000);
    return `Le champ ${field} devrait être après le ${dayjs(parsedDate).format("DD MMMM YYYY")}.`;
  },
});
extend("is", {
  message: (field) => {
    return `${field} n'est pas identique`;
  },
});

localize("fr", fr);

Vue.component("ValidationObserver", ValidationObserver);
Vue.component("ValidationProvider", ValidationProvider);

Vue.component("vue-headful", vueHeadful);

Object.keys(filters).forEach((f) => Vue.filter(f, filters[f]));
Vue.prototype.$filters = filters;

axios.defaults.baseURL = `/api/v1`;
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
  render: (h) => h(App),
  router,
  store,
}).$mount("#app");
