import posthog from 'posthog-js'

export default {
  install(Vue) {
    if (process.env.VUE_APP_POSTHOG_HOST) {
      posthog.init(
        `${process.env.VUE_APP_POSTHOG_KEY}`,
        {
          api_host: `${process.env.VUE_APP_POSTHOG_HOST}`,
          capture_pageview: false,
        }
      )

      Vue.prototype.$posthog = posthog
    }
  }
}
