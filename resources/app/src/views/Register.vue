<template>
  <layout-page name="register" wide bg-color="green" bg-image centered>
    <router-view />
  </layout-page>
</template>

<script>
import Authenticated from '@/mixins/Authenticated';

const routeGuard = (to, from, next) => {
  if (to.name === 'register') {
    return next('/register/1');
  }

  return next();
};

export default {
  name: 'Register',
  mixins: [Authenticated],
  beforeRouteEnter: routeGuard,
  beforeRouteUpdate: routeGuard,
};
</script>

<style lang="scss">
</style>
