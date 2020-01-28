<template>
  <div class="admin-menu">
    <b-nav-item to="/admin">Tableau de bord</b-nav-item>
    <b-nav-item to="/admin/communities">Communautés</b-nav-item>
    <b-nav-item disabled>Membres</b-nav-item>
    <b-nav-item disabled>Véhicules</b-nav-item>
    <b-nav-item disabled>Réservations</b-nav-item>
  </div>
</template>

<script>
export default {
  name: 'AdminMenu',
};
</script>

<style lang="scss">
.admin-menu {
  .nav-item a.nav-link {
    color: $black;

    &.disabled {
      color: rgba(0, 0, 0, 0.3);
    }

    &.router-link-exact-active, &:hover {
      color: $locomotion-green;
    }
  }
}
</style>
