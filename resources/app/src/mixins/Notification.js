import { mapState } from 'vuex';

function displayNotifications(notifications) {
  notifications.forEach((notification) => {
    const h = this.$createElement;
    const vNodesMsg = h(
      'div',
      { domProps: { innerHTML: notification.content } },
    );
    this.$bvToast.toast([vNodesMsg], {
      solid: true,
      title: notification.title,
      toaster: 'b-toaster-top-right',
      variant: notification.variant,
      toastClass: notification.type,
      noAutoHide: notification.variant === 'danger',
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
