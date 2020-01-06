module.exports = {
  root: true,
  'extends': 'stylelint-config-primer',
  rules: {
    'at-rule-blacklist': [],
    'order/properties-order': null,
    'selector-max-type': null,
    'max-nesting-depth': 4,
    'selector-list-comma-newline-after': 'always-multi-line',
  },
};
