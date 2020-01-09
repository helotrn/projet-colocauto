export default {
  async beforeMount() {
    if (!this.loggedIn && this.auth.token) {
      await this.$store.dispatch('loadUser');
    }
  },
  computed: {
    auth() {
      const { token, refreshToken } = this.$store.state;
      return {
        token,
        refreshToken,
      };
    },
    loggedIn() {
      return !!this.user;
    },
    user() {
      return this.$store.state.user;
    },
  },
  methods: {
    logout() {
      this.$store.dispatch('logout');

      this.$store.commit('addNotification', {
        content: "Vous n'êtes plus connecté à Locmotion. À bientôt!",
        title: 'Déconnexion réussie.',
        variant: 'success',
        type: 'logout',
      });

      this.$router.push('/');
    },
    skipToLogin(from) {
      this.$router.replace('/login', { from });
    },
    skipToApp() {
      this.$router.replace('/app');
    },
  },
};
