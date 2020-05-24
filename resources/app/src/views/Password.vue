<template>
  <layout-page name="password" wide bg-color="green" bg-image centered>
    <router-view />
  </layout-page>
</template>

<script>
import Authenticated from '@/mixins/Authenticated';

const routeGuard = (to, from, next) => {
  if (to.name === 'password') {
    return next('/login');
  }

  return next();
};

export default {
  name: 'Password',
  mixins: [Authenticated],
  beforeRouteEnter: routeGuard,
  beforeRouteUpdate: routeGuard,
};
</script>

<style lang="scss">
</style>
