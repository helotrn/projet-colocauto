<template>
  <div class="page login">
    <layout-header class="page__header" />

    <main class="page__content">
      <login-box />
    </main>

    <footer class="page__footer">
      <molotov-footer />
    </footer>
  </div>
</template>

<script>
import Authenticated from '@/mixins/Authenticated';
import Notification from '@/mixins/Notification';

import LayoutHeader from '@/components/Layout/Header.vue';
import LoginBox from '@/components/Login/Box.vue';
import MolotovFooter from '@/components/Molotov/Footer.vue';

export default {
  name: 'Login',
  mixins: [Authenticated, Notification],
  mounted() {
    if (this.loggedIn) {
      this.$router.replace('/app');
    }
  },
  components: {
    LayoutHeader,
    LoginBox,
    MolotovFooter,
  },
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
