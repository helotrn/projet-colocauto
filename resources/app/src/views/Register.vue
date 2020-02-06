<template>
  <layout-page name="register" wide>
    <router-view />
  </layout-page>
</template>

<script>
import Authenticated from '@/mixins/Authenticated';
import Notification from '@/mixins/Notification';

const routeGuard = (to, from, next) => {
  if (to.name === 'register') {
    return next('/register/1');
  }

  return next();
};

export default {
  name: 'Register',
  mixins: [Authenticated, Notification],
  beforeRouteEnter: routeGuard,
  beforeRouteUpdate: routeGuard,
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
