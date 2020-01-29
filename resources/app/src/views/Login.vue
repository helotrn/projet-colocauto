<template>
  <layout-page name="login" wide>
    <login-box />
  </layout-page>
</template>

<script>
import Authenticated from '@/mixins/Authenticated';
import Notification from '@/mixins/Notification';

import LoginBox from '@/components/Login/Box.vue';

export default {
  name: 'Login',
  mixins: [Authenticated, Notification],
  mounted() {
    if (this.loggedIn) {
      this.skipToApp();
    }
  },
  components: { LoginBox },
};
</script>

<style lang="scss">
.page.login {
  main {
    background-color: $locomotion-green;
    display: flex;
    flex-direction: column;
    justify-content: space-around;
    padding: $grid-gutter-width / 2;
  }
}
</style>
