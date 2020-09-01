module.exports = {
  root: true,
  extends: 'stylelint-config-primer',
  rules: {
    'at-rule-blacklist': [],
    'order/properties-order': null,
    'selector-max-type': null,
    'max-nesting-depth': 5,
    'selector-max-id': 1,
    'selector-list-comma-newline-after': 'always-multi-line',
    'plugin/no-unsupported-browser-features': [
      'warn',
      {
        browsers: ['> 5%'],
        ignore: ['rem', 'animation', 'css-transition', 'css-gradients'],
      },
    ],
    'selector-no-qualifying-type': [
      true,
      {
        ignore: ['attribute', 'class'],
      },
    ],
    'selector-max-compound-selectors': 4,
    'selector-max-specificity': '1,5,0',
  },
};
