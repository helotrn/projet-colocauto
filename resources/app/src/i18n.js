import Vue from "vue";
import VueI18n from "vue-i18n";

import messages from "./locales";

Vue.use(VueI18n);

export default new VueI18n({
  locale: "fr",
  fallbackLocale: "fr",
  formatFallbackMessages: true,
  messages,
  numberFormats: {
    fr: {
      currency_cad: {
        style: "currency",
        currency: "CAD",
        currencyDisplay: "symbol",
      },
      // Dollars only, no cents
      dollars_cad: {
        style: "currency",
        currency: "CAD",
        currencyDisplay: "symbol",
        maximumFractionDigits: 0,
      },
      currency_euro: {
        style: "currency",
        currency: "EUR",
        currencyDisplay: "symbol",
      },
      euros_no_cents: {
        style: "currency",
        currency: "EUR",
        currencyDisplay: "symbol",
        maximumFractionDigits: 0,
      },
      percent: {
        style: "percent",
        minimumFractionDigits: 2, // x.xx
      },
    },
  },
});
