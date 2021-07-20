const reportPolicy = process.env.NODE_ENV === "production" ? "error" : "warn";
module.exports = {
  root: true,
  env: {
    node: true,
  },
  extends: ["plugin:vue/essential", "@vue/airbnb"],
  rules: {
    "arrow-body-style": reportPolicy,
    "arrow-parens": reportPolicy,
    "comma-dangle": reportPolicy,
    "consistent-return": reportPolicy,
    indent: reportPolicy,
    "max-len": "warn",
    "no-console": reportPolicy,
    "no-debugger": reportPolicy,
    "no-irregular-whitespace": reportPolicy,
    "no-multiple-empty-lines": [
      "warn",
      {
        max: 2,
      },
    ],
    "no-nested-ternary": reportPolicy,
    "no-plusplus": reportPolicy,
    "no-unreachable": reportPolicy,
    "no-unused-vars": reportPolicy,
    "prefer-destructuring": [
      "error",
      {
        VariableDeclarator: {
          array: false,
          object: true,
        },
        AssignmentExpression: {
          array: false,
          object: false,
        },
      },
      {
        enforceForRenamedProperties: false,
      },
    ],
    "prefer-template": reportPolicy,
    "spaced-comment": reportPolicy,
    semi: reportPolicy,
    "vue/html-closing-bracket-newline": "off",
    "vue/html-end-tags": "off",
    "vue/html-self-closing": "warn",
    "vue/no-parsing-error": [
      2,
      {
        "x-invalid-end-tag": false,
      },
    ],
    "vue/no-unused-components": reportPolicy,
    "vue/max-attributes-per-line": [
      "warn",
      {
        singleline: 6,
        multiline: {
          max: 3,
          allowFirstLine: true,
        },
      },
    ],
    "vue/singleline-html-element-content-newline": "off",
    "vue/html-indent": [
      "warn",
      2,
      {
        attribute: 1,
        baseIndent: 1,
        closeBracket: 0,
        alignAttributesVertically: false,
        ignores: [],
      },
    ],
  },
  parserOptions: {
    parser: "babel-eslint",
  },
};
