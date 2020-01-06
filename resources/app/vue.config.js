const path = require('path');

module.exports = {
  css: {
    loaderOptions: {
      sass: {
        prependData: `@import "@/assets/scss/_variables.scss";`
      },
    },
  },

  assetsDir: 'app/',

  pluginOptions: {
    lintStyleOnBuild: true,
    stylelint: {}
  }
};
