import helpers from '@/helpers';

const { extractErrors } = helpers;

export default {
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
    slug() {
      return this.$route.meta.slug;
    },
  },
  methods: {
    reset() {
      this.$store.commit(`${this.slug}/item`, JSON.parse(this.initialItem));
    },
    async submit() {
      try {
        await this.$store.dispatch(`${this.slug}/updateItem`);
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
