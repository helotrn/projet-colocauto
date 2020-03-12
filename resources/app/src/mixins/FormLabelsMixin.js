export default {
  methods: {
    placeholderOrLabel(key) {
      if (this.$i18n.te(`placeholders.${key}`)) {
        return this.$i18n.t(`placeholders.${key}`);
      }

      return this.label(key);
    },
    label(key) {
      return this.$i18n.t(`fields.${key}`);
    },
  },
};
