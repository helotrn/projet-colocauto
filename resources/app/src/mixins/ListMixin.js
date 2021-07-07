import { extractErrors } from '@/helpers';

export default {
  beforeRouteEnter(to, from, next) {
    next((vm) => {
      if (to.query) {
        Object.keys(to.query).forEach(name => vm.setParam({
          name,
          value: to.query[name],
        }));
      }
    });
  },
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
    selectionIndeterminate() {
      if (this.selectionStatus === true || this.selectionStatus === false) {
        return false;
      }

      return true;
    },
    selectionStatus() {
      if (this.selected.length === 0) {
        return false;
      }

      if (this.selected.length === this.data.length) {
        return true;
      }

      return undefined;
    },
    slug() {
      return this.$route.meta.slug;
    },
    sortDesc: {
      get() {
        // Remove field name to keep sign only.
        return this.contextParams.order[0] === '-';
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
        // Remove the sign to keep field name only.
        return this.contextParams.order.replace('-', '');
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
        })
        .catch((e) => {
          if (e.request) {
            switch (e.request.status) {
              case 422:
                this.$store.commit('addNotification', {
                  content: extractErrors(e.response.data).join(', '),
                  title: 'Erreur de sauvegarde',
                  variant: 'danger',
                  type: 'form',
                });
                break;
              default:
                throw e;
            }
          }

          throw e;
        });
    },
    async exportCSV() {
      await this.$store.dispatch(
        `${this.slug}/export`,
        {
          ...this.routeParams,
          ...this.contextParams,
        },
      );
    },
    loadListData() {
      if (this.routeDataLoaded !== undefined && !this.routeDataLoaded) {
        return true;
      }

      if (this.listDebounce) {
        clearTimeout(this.listDebounce);
      }

      this.listDebounce = setTimeout(() => {
        try {
          this.$store.dispatch(
            `${this.slug}/retrieve`,
            {
              ...this.routeParams,
              ...this.contextParams,
            },
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
    setParam({ name, value }) {
      const query = {
        ...this.$route.query,
        [name]: value,
      };

      if (value === undefined) {
        delete query[name];
      }

      if (JSON.stringify(this.$route.query) !== JSON.stringify(query)) {
        this.$router.replace({
          ...this.$route,
          query,
        });
      }

      this.$store.commit(`${this.slug}/setParam`, { name, value });
    },
  },
  watch: {
    contextParams: {
      deep: true,
      handler() {
        this.loadListData();
      },
    },
  },
};
