module.exports = {
  chainWebpack: (config) => {
    const svgRule = config.module.rule('svg');

    svgRule.uses.clear();

    svgRule
      .use('babel-loader')
      .loader('babel-loader')
      .end()
      .use('vue-svg-loader')
      .loader('vue-svg-loader');

    config.module
      .rule('i18n')
      .resourceQuery(/blockType=i18n/)
      .type('javascript/auto')
      .use('i18n')
      .loader('@kazupon/vue-i18n-loader')
      .end()
      .use('yaml')
      .loader('yaml-loader')
      .end();
  },

  css: {
    loaderOptions: {
      sass: {
        prependData: '@import "@/assets/scss/_variables.scss";',
      },
    },
  },

  devServer: {
    proxy: {
      '^/api': {
        target: ((process.env.IS_HOMESTEAD)
          ? 'http://locomotion.local:8000'
          : 'http://localhost:8000'),
        ws: true,
        changeOrigin: true,
      },
    },
    public: ((process.env.IS_HOMESTEAD)
      ? 'locomotion.local:8080'
      : 'localhost:8080'),
  },

  assetsDir: 'dist/',

  pluginOptions: {
    lintStyleOnBuild: true,
    stylelint: {},
    webpackBundleAnalyzer: {
      openAnalyzer: true,
    },
  },
};
