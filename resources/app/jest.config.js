module.exports = {
  moduleFileExtensions: ["js", "jsx", "json", "vue"],
  transform: {
    ".*\\.vue$": "vue-jest",
    ".+\\.(css|styl|less|sass|scss|svg|png|jpg|ttf|woff|woff2)$": "jest-transform-stub",
    "^.+\\.(js|jsx)?$": "babel-jest",
  },
  moduleNameMapper: {
    "^@/(.*)$": "<rootDir>/src/$1",
    "^axios$": "axios/dist/browser/axios.cjs",
  },
  snapshotSerializers: ["jest-serializer-vue"],
  testMatch: ["<rootDir>/(**/*.test.(js|jsx|ts|tsx)|**/__tests__/*.(js|jsx|ts|tsx))"],
  transformIgnorePatterns: ["<rootDir>/node_modules/(?!vue2-google-maps)"],
};
