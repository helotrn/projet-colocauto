export default {
  methods: {
    placeholderOrLabel(key, prefix) {
      if (this.$i18n.te(`${prefix ? `${prefix}.` : ""}placeholders.${key}`)) {
        return this.$i18n.t(`placeholders.${key}`);
      }

      return this.label(key, prefix);
    },
    label(key, prefix) {
      return this.$i18n.t(`${prefix ? `${prefix}.` : ""}fields.${key}`);
    },
  },
};
