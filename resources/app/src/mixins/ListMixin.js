export default {
  computed: {
    context() {
      return this.$store.state[this.slug];
    },
    contextParams: {
      get() {
        return this.context.params;
      },
      set(val) {
        this.context.params = val;
        this.loadListData();
      },
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
    exportUrl() {
      return this.context.exportUrl;
    },
    fieldsList() {
      return this.fields
        ? this.fields.map(f => f.key)
        : this.$route.meta.data[this.slug].retrieve.fields.split(',');
    },
    filters() {
      return this.context.filters;
    },
    lastPage() {
      return this.context.lastPage;
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
    destroyItemModal(item) {
      const itemLabel = typeof this.itemLabel === 'function'
        ? this.itemLabel(item)
        : 'cet item';
      this.$bvModal.msgBoxConfirm(
        `Êtes-vous sûr de vouloir retirer ${itemLabel}?`,
        {
          size: 'sm',
          buttonSize: 'sm',
          okTitle: 'Oui, retirer',
          cancelTitle: 'Annuler',
          okVariant: 'danger',
          cancelVariant: 'primary',
          footerClass: 'p-2 border-top-0',
          centered: true,
        },
      )
        .then(async (value) => {
          if (value) {
            await this.$store.dispatch(`${this.slug}/destroy`, item.id);
            await this.$store.dispatch(`${this.slug}/load`);
          }
        });
    },
    async exportCSV() {
      await this.$store.dispatch(
        `${this.slug}/export`,
        this.params,
      );
    },
    loadListData() {
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
    resetExportUrl() {
      this.$store.commit(`${this.slug}/exportUrl`, null);
    },
    restoreItemModal(item) {
      const itemLabel = typeof this.itemLabel === 'function'
        ? this.itemLabel(item)
        : 'cet item';
      this.$bvModal.msgBoxConfirm(
        `Êtes-vous sûr de vouloir restaurer ${itemLabel}?`,
        {
          size: 'sm',
          buttonSize: 'sm',
          okTitle: 'Oui, restaurer',
          cancelTitle: 'Annuler',
          okVariant: 'warning',
          cancelVariant: 'primary',
          footerClass: 'p-2 border-top-0',
          centered: true,
        },
      )
        .then(async (value) => {
          if (value) {
            await this.$store.dispatch(`${this.slug}/restore`, item.id);
            await this.$store.dispatch(`${this.slug}/load`);
          }
        });
    },
    rowSelected(items) {
      this.selected = items;
    },
  },
};
