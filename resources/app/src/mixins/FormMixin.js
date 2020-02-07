import helpers from '@/helpers';

const { extractErrors } = helpers;

export default {
  beforeRouteLeave(to, from, next) {
    this.$store.commit(`${this.slug}/item`, null);
    this.$store.commit(`${this.slug}/initialItem`, '');
    next();
  },
  async mounted() {
    await this.loadItem();
  },
  computed: {
    changed() {
      return this.initialItem !== JSON.stringify(this.item);
    },
    context() {
      return this.$store.state[this.slug];
    },
    form() {
      return this.$route.meta.form;
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
      return this.context.loading;
    },
    slug() {
      return this.$route.meta.slug;
    },
  },
  methods: {
    async loadItem() {
      const { dispatch } = this.$store;

      if (this.id === 'new') {
        await dispatch(`${this.slug}/loadEmpty`);
      } else {
        await dispatch(`${this.slug}/retrieveOne`, {
          id: this.id,
          params: this.$route.meta.params,
        });
      }
    },
    reset() {
      this.$store.commit(`${this.slug}/item`, JSON.parse(this.initialItem));
    },
    async submit() {
      try {
        await this.$store.dispatch(`${this.slug}/updateItem`, this.$route.meta.params);
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
};
