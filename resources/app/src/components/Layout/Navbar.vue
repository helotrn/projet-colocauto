<template>
  <b-navbar class="layout-navbar" toggleable="lg" variant="transparent" type="light">
    <div class="layout-navbar__brand-wrapper">
      <b-navbar-brand to="/">
        <img class="layout-navbar__logo" src="@/assets/img/logo.png">
      </b-navbar-brand>
      <b-navbar-brand class="layout-navbar__separator" v-if="pageTitle"/>
        <b-navbar-brand>{{ pageTitle }}</b-navbar-brand>
    </div>

    <b-navbar-toggle target="nav-collapse" />

    <b-collapse id="nav-collapse" is-nav>
      <b-navbar-nav class="ml-auto">
        <b-nav-item to="/app">Tableau de bord</b-nav-item>
        <b-nav-item href="/map" v-if="hasCommunity">Trouver un véhicule</b-nav-item>
        <b-nav-item to="/community" v-if="hasCommunity">Communauté</b-nav-item>
        <b-nav-item href="/map" v-if="!hasCommunity">Trouver une communauté</b-nav-item>
      </b-navbar-nav>
    </b-collapse>
  </b-navbar>
</template>

<script>
import Authenticated from '@/mixins/Authenticated';

export default {
  name: 'Navbar',
  mixins: [Authenticated],
  computed: {
    hasCommunity() {
      return this.loggedIn;
    },
    pageTitle() {
      return this.$route.meta && this.$route.meta.title;
    },
  },
};
</script>

<style lang="scss">
.layout-navbar {
  &__logo {
    max-height: 22px;
    vertical-align: baseline;
  }

  &__brand-wrapper {
    display: flex;
  }

  .navbar-brand:hover {
    color: $locomotion-green;
  }

  &__separator {
    width: 1px;
    height: 36px;
    background-color: $locomotion-green;
  }
}
</style>
