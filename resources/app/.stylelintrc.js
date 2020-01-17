module.exports = {
  root: true,
  extends: 'stylelint-config-primer',
  rules: {
    'at-rule-blacklist': [],
    'order/properties-order': null,
    'selector-max-type': null,
    'max-nesting-depth': 4,
    'selector-list-comma-newline-after': 'always-multi-line',
    'plugin/no-unsupported-browser-features': ['warn', {
      browsers: ['> 5%'],
      ignore: ['rem', 'animation', 'css-transition'],
    }],
    'selector-no-qualifying-type': [true, {
      ignore: ['attribute', 'class'],
    }],
    'selector-max-compound-selectors': 4,
    'selector-max-specificity': '0,5,0',
  },
};
