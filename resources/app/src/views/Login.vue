<template>
  <div class="login page">
    <layout-header class="page__header" />

    <main class="page__content">
      <login-box />
    </main>

    <layout-footer class="page__footer" />
  </div>
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
  }
}
</style>
