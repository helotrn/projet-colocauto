<template>
  <div class="admin-sidebar">
    <b-nav-item to="/admin">{{ $t("titles.dashboard") | capitalize }}</b-nav-item>
    <b-nav-item to="/admin/communities">Communautés</b-nav-item>
    <b-nav-item to="/admin/users">Membres</b-nav-item>
    <b-nav-item to="/admin/loanables">Véhicules</b-nav-item>
    <b-nav-item to="/admin/loans">Emprunts</b-nav-item>
    <b-nav-item to="/admin/invoices" v-if="isGlobalAdmin">Factures</b-nav-item>
    <b-nav-item to="/admin/padlocks" v-if="isGlobalAdmin">Cadenas</b-nav-item>
    <b-nav-item to="/admin/tags" v-if="isGlobalAdmin">Mots-clés</b-nav-item>
    <b-nav-item to="/admin/invitations" v-if="isGlobalAdmin">Invitations</b-nav-item>
    <b-nav-item to="/admin/expenses" v-if="isGlobalAdmin">Dépenses</b-nav-item>
  </div>
</template>

<script>
export default {
  name: "AdminSidebar",
  props: {
    isGlobalAdmin: {
      required: true,
      type: Boolean,
    },
  },
};
</script>

<style lang="scss">
.admin-sidebar {
  .nav-item a.nav-link {
    color: $black;

    &.disabled {
      color: $grey;
    }

    &.router-link-exact-active,
    &:hover {
      color: $locomotion-green;
    }
  }
}
</style>
