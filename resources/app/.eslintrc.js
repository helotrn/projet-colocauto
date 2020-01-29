const reportPolicy = process.env.NODE_ENV === 'production' ? 'error' : 'warn';
module.exports = {
  root: true,
  env: {
    node: true,
  },
  extends: [
    'plugin:vue/essential',
    '@vue/airbnb',
  ],
  rules: {
    'no-console': reportPolicy,
    'no-debugger': reportPolicy,
    'no-unreachable': reportPolicy,
    'comma-dangle': reportPolicy,
    semi: reportPolicy,
    indent: reportPolicy,
    'spaced-comment': reportPolicy,
    'no-unused-vars': reportPolicy,
    'vue/no-unused-components': reportPolicy,
    'no-irregular-whitespace': reportPolicy,
    'consistent-return': reportPolicy,
    'prefer-template': reportPolicy,
    'no-nested-ternary': reportPolicy,
    'no-plusplus': reportPolicy,
    'max-len': 'warn',
    'vue/html-closing-bracket-newline': 'off',
    'no-multiple-empty-lines': ['warn', {
      max: 2,
    }],
    'prefer-destructuring': ['error', {
      VariableDeclarator: {
        array: false,
        object: true,
      },
      AssignmentExpression: {
        array: false,
        object: false,
      },
    }, {
      enforceForRenamedProperties: false,
    }],
    'vue/html-self-closing': 'warn',
    'vue/no-parsing-error': [2, {
      'x-invalid-end-tag': false,
    }],
    'vue/html-end-tags': 'off',
    'vue/valid-template-root': 'off',
    'vue/max-attributes-per-line': ['warn', {
      singleline: 6,
      multiline: {
        max: 3,
        allowFirstLine: true,
      },
    }],
    'vue/singleline-html-element-content-newline': 'off',
    'vue/html-indent': ['warn', 2, {
      attribute: 1,
      baseIndent: 1,
      closeBracket: 0,
      alignAttributesVertically: false,
      ignores: [],
    }],
  },
  parserOptions: {
    parser: 'babel-eslint',
  },
};
