export default {
  computed: {
    context() {
      return this.$store.state[this.slug];
    },
    data() {
      return this.context.data;
    },
    fieldsList() {
      return this.fields.map(f => f.key);
    },
    jsonParams() {
      return JSON.stringify(this.params);
    },
    loading() {
      return !!this.context.ajax;
    },
    slug() {
      return this.$route.meta.slug;
    },
    sortDesc: {
      get() {
        return this.params.order[0] === '-';
      },
      set(desc) {
        this.$store.commit(`${this.slug}/setOrder`, {
          field: this.sortBy,
          direction: desc ? 'desc' : 'asc',
        });
      },
    },
    sortBy: {
      get() {
        return this.params.order.replace('-', '');
      },
      set(field) {
        this.$store.commit(`${this.slug}/setOrder`, {
          field,
          direction: 'asc',
        });
      },
    },
    params() {
      return this.context.params;
    },
  },
  methods: {
    rowSelected(items) {
      this.selected = items;
    },
  },
  watch: {
    jsonParams() {
      this.$store.dispatch(`${this.slug}/retrieve`, { fields: this.fieldsList.join(',') });
    },
  },
};
