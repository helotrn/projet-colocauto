import { mapState } from 'vuex';

export default {
  watch: {
    notifications(notifications) {
      notifications.forEach((notification) => {
        this.$bvToast.toast(notification.content, {
          solid: true,
          title: notification.title,
          toaster: 'b-toaster-top-right',
          variant: notification.variant,
          toastClass: notification.type,
        });
        this.$store.commit('removeNotification', notification);
      });
    },
  },
  computed: mapState(['notifications']),
};
