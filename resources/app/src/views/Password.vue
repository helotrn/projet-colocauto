<template>
  <layout-page name="password" wide bg-color="green" bg-image centered>
    <router-view />
  </layout-page>
</template>

<script>
const routeGuard = (to, from, next) => {
  if (to.name === 'password') {
    return next('/login');
  }

  return next();
};

export default {
  name: 'Password',
  beforeRouteEnter: routeGuard,
  beforeRouteUpdate: routeGuard,
};
</script>

<style lang="scss">
</style>
