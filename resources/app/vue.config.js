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
        target: 'http://localhost:8000',
        ws: true,
        changeOrigin: true,
      },
    },
  },

  assetsDir: 'app/',

  pluginOptions: {
    lintStyleOnBuild: true,
    stylelint: {},
  },
};
