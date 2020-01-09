export default {
  async beforeMount() {
    await this.$store.dispatch('loadUser');
  },
  computed: {
    loggedIn() {
      return !!this.user;
    },
    user() {
      return this.$store.state.user;
    },
  },
  methods: {
    skipToLogin(from) {
      this.$router.replace('/login', { from });
    },
    skipToApp() {
      this.$router.replace('/app');
    },
  },
};
