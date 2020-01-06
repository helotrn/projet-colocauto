import { mapState } from 'vuex';

export default {
  watch: {
    notification(notification, _) {
      if (notification) {
        const toast = this.$bvToast.toast(notification.content, {
          solid: true,
          title: notification.title,
          toaster: 'b-toaster-top-center',
          variant: notification.variant,
          toastClass: notification.type,
        });
      }
      this.$store.commit('notification', null);
    },
  },
  computed: mapState(['notification']),
};
