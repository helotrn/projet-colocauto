export default {
  computed: {
    context() {
      return this.$store.state[this.slug];
    },
    creatable() {
      return this.$route.meta.creatable;
    },
    data() {
      return this.context.data;
    },
    fieldsList() {
      return this.fields.map(f => f.key);
    },
    filters() {
      return this.context.filters;
    },
    jsonParams() {
      return JSON.stringify(this.params);
    },
    loading() {
      return !!this.context.ajax;
    },
    params() {
      return this.context.params;
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
    total() {
      return this.context.total;
    },
  },
  data() {
    return {
      listDebounce: null,
    };
  },
  methods: {
    rowSelected(items) {
      this.selected = items;
    },
  },
  watch: {
    jsonParams() {
      if (this.listDebounce) {
        clearTimeout(this.listDebounce);
      }

      this.listDebounce = setTimeout(() => {
        this.$store.dispatch(
          `${this.slug}/retrieve`,
          {
            fields: this.fieldsList.join(','),
          },
        );
        this.listDebounce = null;
      }, 250);
    },
  },
};
