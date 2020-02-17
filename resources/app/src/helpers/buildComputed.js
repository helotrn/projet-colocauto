export default function buildComputed(id, fields) {
  return fields.reduce((acc, field) => {
    acc[field] = (function buildComputedClosure(i, f) {
      return {
        get() {
          return this.$store.state[i][f];
        },
        set(value) {
          this.$store.commit(`${i}/${f}`, value);
        },
      };
    }(id, field));

    return acc;
  }, {});
}
