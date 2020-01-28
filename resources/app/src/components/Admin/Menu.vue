<template>
  <div class="admin-menu">
    <b-nav-item to="/admin">Tableau de bord</b-nav-item>
    <b-nav-item to="/admin/communities">Communautés</b-nav-item>
    <b-nav-item>Membres</b-nav-item>
    <b-nav-item>Véhicules</b-nav-item>
    <b-nav-item>Réservations</b-nav-item>
  </div>
</template>

<script>
export default {
  name: 'AdminMenu',
};
</script>

<style lang="scss">
.admin-menu {
  .nav-item a {
    color: $black;

    &.router-link-exact-active, &:hover {
      color: $locomotion-green;
    }
  }
}
</style>
