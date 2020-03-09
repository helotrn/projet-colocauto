import { extractErrors } from '@/helpers';

export default {
  beforeRouteLeave(to, from, next) {
    if (!from.meta.skipCleanup) {
      this.$store.commit(`${this.slug}/item`, null);
      this.$store.commit(`${this.slug}/initialItem`, '');
    }

    next();
  },
  beforeRouteEnter(to, from, next) {
    next((vm) => {
      vm.loadItem();
    });
  },
  computed: {
    changed() {
      return this.initialItem !== JSON.stringify(this.item);
    },
    context() {
      return this.$store.state[this.slug];
    },
    form() {
      return this.context.form || this.$route.meta.form;
    },
    initialItem() {
      return this.context.initialItem;
    },
    item: {
      get() {
        return this.context.item;
      },
      set(item) {
        this.$store.commit(`${this.slug}/item`, item);
      },
    },
    loading() {
      return !!this.context.ajax;
    },
    params() {
      return this.$route.meta.params;
    },
    slug() {
      return this.$route.meta.slug;
    },
  },
  methods: {
    async loadItem() {
      const { dispatch } = this.$store;

      try {
        if (this.id === 'new') {
          await dispatch(`${this.slug}/loadEmpty`);
        } else {
          await dispatch(`${this.slug}/retrieveOne`, {
            id: this.id,
            params: this.params,
          });
        }
      } catch (e) {
        switch (e.request.status) {
          case 401:
            this.$store.commit('addNotification', {
              content: "Vous n'êtes pas connecté.",
              title: 'Non connecté',
              variant: 'warning',
              type: 'login',
            });
            this.$store.commit('user', null);
            this.$router.push(`/login?r=${this.$route.fullPath}`);
            break;
          default:
            this.$store.commit('addNotification', {
              content: 'Erreur fatale',
              title: 'Erreur fatale',
              variant: 'danger',
              type: 'form',
            });
            break;
        }
      }
    },
    reset() {
      this.$store.commit(`${this.slug}/item`, JSON.parse(this.initialItem));
    },
    async submit() {
      try {
        if (!this.item.id) {
          await this.$store.dispatch(`${this.slug}/createItem`, this.params);
        } else {
          await this.$store.dispatch(`${this.slug}/updateItem`, this.params);
        }
      } catch (e) {
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
            this.$store.commit('addNotification', {
              content: 'Erreur fatale',
              title: 'Erreur de sauvegarde',
              variant: 'danger',
              type: 'form',
            });
            break;
        }
      }
    },
  },
  props: {
    id: {
      type: String,
      required: true,
    },
  },
  watch: {
    item: {
      deep: true,
      handler(newValue, oldValue) {
        if (oldValue === null && newValue) {
          if (this.$route.hash) {
            setTimeout(() => {
              const el = document.getElementById(this.$route.hash.substring(1));
              if (el) {
                this.$scrollTo(el);
              }
            }, 100);
          }
        }
        this.$store.commit(`${this.slug}/item`, this.item);
      },
    },
  },
};
