import { mapState } from 'vuex';

function displayNotifications(notifications) {
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
}

export default {
  mounted() {
    displayNotifications.call(this, this.$store.state.notifications);
  },
  watch: {
    notifications(notifications) {
      displayNotifications.call(this, notifications);
    },
  },
  computed: mapState(['notifications']),
};
