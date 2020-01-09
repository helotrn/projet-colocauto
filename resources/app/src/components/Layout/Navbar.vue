<template>
  <b-navbar class="layout-navbar" toggleable="lg" variant="transparent" type="light">
    <div class="layout-navbar__brand-wrapper">
      <b-navbar-brand to="/">
        <img class="layout-navbar__logo" src="/logo.png">
      </b-navbar-brand>
      <b-navbar-brand class="layout-navbar__separator" v-if="pageTitle"/>
      <b-navbar-brand>{{ pageTitle }}</b-navbar-brand>
    </div>

    <b-navbar-toggle target="nav-collapse" />

    <b-collapse id="nav-collapse" is-nav>
      <b-navbar-nav class="ml-auto" v-if="loggedIn">
        <b-nav-item to="/app">Tableau de bord</b-nav-item>
        <b-nav-item href="/map" v-if="hasCommunity">Trouver un véhicule</b-nav-item>
        <b-nav-item to="/community" v-if="hasCommunity">Communauté</b-nav-item>
        <b-nav-item href="/map" v-if="!hasCommunity">Trouver une communauté</b-nav-item>

        <b-nav-item-dropdown class="layout-navbar__dropdown" text="" right>
          <template v-slot:button-content>
            <b-badge pill variant="locomotion" class="layout-navbar__dropdown__icon">
              <svg-profile />
            </b-badge>
          </template>
          <b-dropdown-item href="#">Compte</b-dropdown-item>
          <b-dropdown-item href="#">Aide</b-dropdown-item>
          <b-dropdown-divider />
          <b-dropdown-item @click="logout">Déconnexion</b-dropdown-item>
        </b-nav-item-dropdown>
      </b-navbar-nav>
      <b-navbar-nav class="ml-auto" v-else>
        <b-nav-item to="/login">Se connecter</b-nav-item>
      </b-navbar-nav>
    </b-collapse>
  </b-navbar>
</template>

<script>
import Authenticated from '@/mixins/Authenticated';

import Profile from '@/assets/svg/profile.svg';

export default {
  name: 'Navbar',
  mixins: [Authenticated],
  components: {
    'svg-profile': Profile,
  },
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

  &.navbar-light .navbar-nav {
    .layout-navbar__dropdown {
      margin-left: 60px;

      .nav-link {
        padding: 0;
        padding-right: 0;
        padding-left: 0;
      }

      &__icon {
        width: $line-height-base + (2 * $nav-link-padding-y);
        height: $line-height-base + (2 * $nav-link-padding-y);
        padding: 9px;
      }
    }
  }
}
</style>
