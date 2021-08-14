const { BundleAnalyzerPlugin } = require("webpack-bundle-analyzer");

const plugins = [];

if (process.env.WEBPACK_ANALYZE) {
  plugins.push(new BundleAnalyzerPlugin());
}

module.exports = {
  chainWebpack: (config) => {
    const svgRule = config.module.rule("svg");

    svgRule.uses.clear();

    svgRule
      .use("babel-loader")
      .loader("babel-loader")
      .end()
      .use("vue-svg-loader")
      .loader("vue-svg-loader");

    config.module
      .rule("i18n")
      .resourceQuery(/blockType=i18n/)
      .type("javascript/auto")
      .use("i18n")
      .loader("@kazupon/vue-i18n-loader")
      .end()
      .use("yaml")
      .loader("yaml-loader")
      .end();
  },

  css: {
    loaderOptions: {
      sass: {
        prependData: '@import "@/assets/scss/_variables.scss";',
      },
    },
  },

  configureWebpack: {
    devServer: {
      proxy: {
        "^/api": {
          target: process.env.BACKEND_URL,
          ws: true,
          changeOrigin: true,
        },
      },
      public: process.env.VUE_APP_FRONTEND_URL,
      disableHostCheck: true,
    },
    plugins,
  },

  assetsDir: "dist/",

  pluginOptions: {
    webpackBundleAnalyzer: {
      openAnalyzer: true,
    },
  },
};
