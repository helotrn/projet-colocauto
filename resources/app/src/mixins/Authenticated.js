export default {
  beforeRouteEnter(to, from, next) {
    next(async (vm) => {
      if (vm.auth.token) {
        if (!vm.$store.state.loaded && !vm.$store.state.loading) {
          try {
            await vm.$store.dispatch("loadUser");
            if (to.name === "login") {
              vm.skipToApp();
            }
          } catch (e) {
            vm.$store.commit("user", null);
            if (to.meta.auth) {
              vm.$router.push(`/login?r=${to.fullPath}`);
            }
          }
        }
      } else if (to.meta.auth) {
        vm.$store.commit("user", null);
        vm.$router.push(`/login?r=${to.fullPath}`);
      }
    });
  },
  computed: {
    auth() {
      const { token, refreshToken } = this.$store.state;
      return {
        token,
        refreshToken,
      };
    },
    user() {
      return this.$store.state.user;
    },
  },
  methods: {
    skipToLogin() {
      this.$router.replace("/login");
    },
    skipToApp() {
      if (this.$route.query.r) {
        this.$router.replace(this.$route.query.r);
      } else {
        this.$router.replace("/app");
      }
    },
  },
};
