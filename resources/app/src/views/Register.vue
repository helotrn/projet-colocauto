<template>
  <layout-page name="register" wide>
    <register-box :current-page="stepIndex" />
  </layout-page>
</template>

<script>
import Authenticated from '@/mixins/Authenticated';
import Notification from '@/mixins/Notification';

import RegisterBox from '@/components/Register/Box.vue';

export default {
  name: 'Signup',
  mixins: [Authenticated, Notification],
  components: { RegisterBox },
  mounted() {
    if (this.loggedIn) {
      if (!this.registered) {
        this.$router.replace('/register/2');
      } else {
        this.$router.replace('/app');
      }
    }
  },
  props: {
    step: {
      type: String,
      required: false,
      default: '1',
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
.page.register {
  main {
    background-color: $locomotion-green;
    display: flex;
    flex-direction: column;
    justify-content: space-around;
  }
}
</style>
