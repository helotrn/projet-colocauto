export default {
  computed: {
    loggedIn() {
      return !!this.$store.user;
    },
  },
};
