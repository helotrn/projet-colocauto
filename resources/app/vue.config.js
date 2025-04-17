const { BundleAnalyzerPlugin } = require("webpack-bundle-analyzer");
const path = require('path');

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
        prependData: '@import "@theme/scss/_variables.scss";',
      },
    },
  },

  configureWebpack: (config) => {
    config.devServer = {
      proxy: {
        "^/api": {
          target: process.env.BACKEND_URL,
          ws: true,
          changeOrigin: true,
        },
      },
      public: process.env.VUE_APP_FRONTEND_URL,
      disableHostCheck: true,
    };

    // from https://github.com/vuejs/vue-cli/issues/2978#issuecomment-577364101
    if (process.env.NODE_ENV === "development") {
      // See available sourcemaps:
      // https://webpack.js.org/configuration/devtool/#devtool
      config.devtool = "eval-source-map";
      // console.log(`NOTICE: vue.config.js directive: ${config.devtool}`)

      config.output.devtoolModuleFilenameTemplate = (info) => {
        let resPath = info.resourcePath;
        let isVue = resPath.match(/\.vue$/);
        let isGenerated = info.allLoaders;

        let generated = `webpack-generated:///${resPath}?${info.hash}`;
        let vuesource = `vue-source:///${resPath}`;

        return isVue && isGenerated ? generated : vuesource;
      };

      config.output.devtoolFallbackModuleFilenameTemplate = "webpack:///[resource-path]?[hash]";
    }
    if (process.env.WEBPACK_ANALYZE) {
      config.plugins.push(new BundleAnalyzerPlugin());
    }
    config.resolve.alias['@theme'] = path.resolve(__dirname, `src/assets/themes/${process.env.VUE_APP_THEME}`);
  },

  assetsDir: "dist/",

  pluginOptions: {
    webpackBundleAnalyzer: {
      openAnalyzer: true,
    },
  },
};
