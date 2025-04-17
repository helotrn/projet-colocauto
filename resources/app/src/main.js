import Vue from "vue";
import { BootstrapVue, BootstrapVueIcons } from "bootstrap-vue";
import axios from "axios";
import VueAxios from "vue-axios";
import * as VueGoogleMaps from "vue2-google-maps";
import vueHeadful from "vue-headful";
import VueScrollTo from "vue-scrollto";
import VueTheMask from "vue-the-mask";
import * as Sentry from "@sentry/vue";
import VueGtag from "vue-gtag";
import PosthogPlugin from './plugins/posthog';
import posthog from 'posthog-js';

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

import "@theme/scss/main.scss";
import "vue-cal/dist/vuecal.css";

if (process.env.NODE_ENV !== "development" && process.env.VUE_APP_SENTRY_DSN) {
  Sentry.init({
    dsn: process.env.VUE_APP_SENTRY_DSN,
    release: process.env.VUE_APP_RELEASE,
    integrations: [Sentry.vueIntegration({ Vue, attachProps: true, logErrors: true })],
  });
}

Vue.config.productionTip = false;

Vue.use(VueTheMask);
Vue.use(VueScrollTo);
Vue.use(BootstrapVue);
Vue.use(BootstrapVueIcons);
Vue.use(PosthogPlugin);

router.beforeEach((to, from, next) => {
  posthog.capture('$pageleave')
  next()
})

router.afterEach(() => {
  Vue.nextTick(() => {
    posthog.capture('$pageview')
  })
})

if (process.env.VUE_APP_GOOGLE_MAPS_API_KEY) {
  Vue.use(VueGoogleMaps, {
    load: {
      key: process.env.VUE_APP_GOOGLE_MAPS_API_KEY,
      region: "CA",
      libraries: "drawing,places",
      installComponents: true,
    },
  });
}

// Google Analytics configuration
if (process.env.VUE_APP_GA_MEASUREMENT_ID) {
  Vue.use(
    VueGtag,
    {
      config: { id: process.env.VUE_APP_GA_MEASUREMENT_ID },
    },
    router
  );
}

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
extend("decimal", {
  validate: (value, { decimals = '*', separator = '.' } = {}) => {
    if (value === null || value === undefined || value === '') {
      return {
        valid: false
      };
    }
    if (Number(decimals) === 0) {
      return {
        valid: /^-?\d*$/.test(value),
      };
    }
    const regexPart = decimals === '*' ? '+' : `{1,${decimals}}`;
    const regex = new RegExp(`^[-+]?\\d*(\\${separator}\\d${regexPart})?([eE]{1}[-]?\\d+)?$`);

    return {
      valid: regex.test(value),
    };
  },
  message: 'Le champ {_field_} doit être un nombre décimal.'
})

localize("fr", fr);

Vue.component("ValidationObserver", ValidationObserver);
Vue.component("ValidationProvider", ValidationProvider);

Vue.component("vue-headful", vueHeadful);

Object.keys(filters).forEach((f) => Vue.filter(f, filters[f]));
Vue.prototype.$filters = filters;

axios.defaults.baseURL = `${process.env.VUE_APP_BACKEND_URL}/api/v1`;
axios.interceptors.request.use((config) => {
  if (store.state.token) {
    // eslint-disable-next-line
    config.headers.Authorization = `Bearer ${store.state.token}`;
  }
  return config;
});
Vue.use(VueAxios, axios);

/**
 * Global clock. This allows us to use `this.$second` in any vue component
 * either as a watch or a reactive property to depend on in computed attributes.
 *
 * Using this ensures all time-based UI (disabling buttons before time etc.) updates
 * at the same time.
 */
const clock = Vue.observable({ second: dayjs() });
Object.defineProperties(Vue.prototype, {
  $dayjs: {
    get() {
      return dayjs;
    },
  },
  $second: {
    get() {
      return clock.second;
    },
    set(value) {
      clock.second = value;
    },
  },
});
Vue.dayjs = dayjs;

window.setInterval(() => {
  Vue.prototype.$second = dayjs();
}, 1000);

export const app = new Vue({
  i18n,
  render: (h) => h(App),
  router,
  store,
});

app.$mount("#app");
