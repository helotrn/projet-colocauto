<template>
  <layout-page name="register" wide>
    <register-box />
  </layout-page>
</template>

<script>
import Authenticated from '@/mixins/Authenticated';
import Notification from '@/mixins/Notification';

import RegisterBox from '@/components/Register/Box.vue';

export default {
  name: 'Signup',
  mixins: [Authenticated, Notification],
  mounted() {
    if (this.loggedIn) {
      this.skipToApp();
    }
  },
  components: { RegisterBox },
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
