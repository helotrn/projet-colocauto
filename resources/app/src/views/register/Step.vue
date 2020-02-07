<template>
  <register-box :current-page="stepIndex" />
</template>

<script>
import Authenticated from '@/mixins/Authenticated';
import Notification from '@/mixins/Notification';

import RegisterBox from '@/components/Register/Box.vue';

export default {
  name: 'RegisterStep',
  mixins: [Authenticated, Notification],
  components: { RegisterBox },
  beforeRouteEnter(to, from, next) {
    next((vm) => {
      if (vm.isLoggedIn) {
        if (!vm.isRegistered) {
          return vm.$router.replace('/register/2');
        }

        if (vm.user.communities.length === 0) {
          return vm.$router.replace('/register/map');
        }

        return vm.$router.replace('/register/3');
      }

      return vm.$router.push('/');
    });
  },
  props: {
    step: {
      type: String,
      required: true,
    },
  },
  computed: {
    stepIndex() {
      const stepIndex = parseInt(this.step, 10);

      if (Number.isNaN(stepIndex)) {
        return 1;
      }

      return stepIndex;
    },
  },
};
</script>

<style lang="scss">
</style>
