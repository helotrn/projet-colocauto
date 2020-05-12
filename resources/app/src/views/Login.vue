<template>
  <layout-page name="login" wide bg-color="green" bg-image centered>
    <login-box />
  </layout-page>
</template>

<script>
import Authenticated from '@/mixins/Authenticated';

import LoginBox from '@/components/Login/Box.vue';

export default {
  name: 'Login',
  mixins: [Authenticated],
  beforeRouteEnter(to, from, next) {
    next((vm) => {
      if (vm.isLoggedIn) {
        vm.skipToApp();
      }

      if (to.query.token) {
        vm.$store.commit('token', to.query.token);
        vm.$router.replace('/');
      }
    });
  },
  components: { LoginBox },
};
</script>

<style lang="scss">
</style>
