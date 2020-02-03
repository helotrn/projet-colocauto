export default {
  async beforeMount() {
    if (this.auth.token) {
      if (!this.$store.state.loaded && !this.$store.state.loading) {
        await this.$store.dispatch('loadUser');
      }
    } else if (this.$route.meta.auth) {
      this.$router.push('/login');
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
    isAdmin() {
      return this.user.role === 'admin';
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
        content: "Vous n'êtes plus connecté à Locomotion. À bientôt!",
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
