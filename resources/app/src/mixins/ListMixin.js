export default {
  computed: {
    context() {
      return this.$store.state[this.slug];
    },
    contextParams() {
      return this.context.params;
    },
    creatable() {
      return this.$route.meta.creatable;
    },
    data() {
      return this.context.data;
    },
    error() {
      return this.context.error;
    },
    fieldsList() {
      return this.fields
        ? this.fields.map(f => f.key)
        : this.$route.meta.data[this.slug].retrieve.fields.split(',');
    },
    filters() {
      return this.context.filters;
    },
    jsonParams() {
      return JSON.stringify(this.params);
    },
    loaded() {
      return this.context.loaded;
    },
    loading() {
      return !!this.context.ajax;
    },
    params() {
      return {
        ...this.routeParams,
        ...this.contextParams,
      };
    },
    routeParams() {
      return this.$route.meta.data[this.slug].retrieve || {};
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
      firstLoad: true,
      selected: [],
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
      if (this.firstLoad && this.skipListMixinFirstLoad) {
        this.firstLoad = false;
        return false;
      }

      if (this.listDebounce) {
        clearTimeout(this.listDebounce);
      }

      this.listDebounce = setTimeout(() => {
        try {
          this.$store.dispatch(
            `${this.slug}/retrieve`,
            this.params,
          );
          this.listDebounce = null;
        } catch (e) {
          this.$store.commit('addNotification', {
            content: "Vous n'êtes pas connecté.",
            title: 'Non connecté',
            variant: 'warning',
            type: 'login',
          });
          this.$store.commit('user', null);
          this.$router.push(`/login?r=${this.$route.fullPath}`);
        }
      }, 250);

      return true;
    },
  },
};
