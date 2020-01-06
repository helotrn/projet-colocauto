export default {
  computed: {
    loggedIn() {
      return !!this.$store.state.user;
    },
  },
};
