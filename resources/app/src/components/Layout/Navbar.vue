<template>
  <b-navbar
    class="layout-navbar"
    toggleable="lg"
    variant="transparent"
    type="light">
    <div class="layout-navbar__brand-wrapper">
      <b-navbar-brand :to="isLoggedIn ? '/app' : '/'" class="d-none d-lg-block">
        <svg-logo class="layout-navbar__logo" />
      </b-navbar-brand>
      <b-navbar-brand :to="isLoggedIn ? '/app' : '/'" class="d-lg-none">
        <svg-small-logo class="layout-navbar__logo" />
      </b-navbar-brand>

      <b-navbar-brand class="layout-navbar__separator" v-if="title" />
      <b-navbar-brand class="navbar-brand--title" v-if="title">{{ title }}</b-navbar-brand>
    </div>

    <div :class="{
      'navbar-toggle-wrapper': true,
      'navbar-toggle-wrapper--active': toggleMenu ,
      'd-lg-none': true,
    }">
      <b-navbar-toggle
        target="nav-collapse"
        @click="toggleMenu = !toggleMenu" />
    </div>

    <b-collapse id="nav-collapse" class="layout-navbar__collapse" is-nav>
      <div class="layout-navbar__collapse__illustration d-md-none" />

      <b-navbar-nav class="ml-auto" v-if="isLoggedIn">
        <b-nav-item to="/app" v-if="isRegistered">
          <span class="nav-link__icon d-lg-none">
            <svg-dashboard />
          </span>
          <span class="nav-link__text">{{ $t('titles.dashboard') | capitalize }}</span>
        </b-nav-item>

        <b-nav-item to="/community/map" v-if="canLoanVehicle">
          <span class="nav-link__icon d-lg-none">
            <svg-location />
          </span>
          <span class="nav-link__text">Trouver un véhicule</span>
        </b-nav-item>

        <b-nav-item to="/community" v-if="hasCommunity">
          <span class="nav-link__icon d-lg-none">
            <svg-hand />
          </span>
          <span class="nav-link__text">Voisinage</span>
        </b-nav-item>

        <b-nav-item to="/register" v-if="!isAdmin && !hasCompletedRegistration">
          <span class="nav-link__icon d-lg-none">
            <svg-hand />
          </span>
          <span class="nav-link__text">Inscription</span>
        </b-nav-item>

        <b-nav-item to="/profile" class="d-block d-lg-none" v-if="hasCompletedRegistration">
          <span class="nav-link__icon">
            <svg-profile />
          </span>
          <span class="nav-link__text">Profil</span>
        </b-nav-item>

        <b-nav-item to="/help" class="d-block d-lg-none" >
          <span class="nav-link__icon">
            <svg-help />
          </span>
          <span class="nav-link__text">Aide</span>
        </b-nav-item>

        <b-nav-item @click="logout" class="d-block d-lg-none">
          <span class="nav-link__icon">
            <svg-logout />
          </span>
          <span class="nav-link__text">Déconnexion</span>
        </b-nav-item>

        <b-nav-item-dropdown class="layout-navbar__admin" text="Admin" right v-if="isAdmin">
          <admin-sidebar />
        </b-nav-item-dropdown>

        <locale-switcher />

        <b-nav-item-dropdown class="layout-navbar__dropdown d-none d-lg-block" text="" right>
          <template v-slot:button-content>
            <b-badge pill variant="locomotion" class="layout-navbar__dropdown__icon">
              <svg-profile />
            </b-badge>
          </template>
          <b-dropdown-item to="/profile" v-if="hasCompletedRegistration">
            Profil
          </b-dropdown-item>
          <b-dropdown-item to="/help">Aide</b-dropdown-item>
          <b-dropdown-divider />
          <b-dropdown-item @click="logout">Déconnexion</b-dropdown-item>
        </b-nav-item-dropdown>
      </b-navbar-nav>

      <b-navbar-nav class="ml-auto" v-else>
        <b-nav-item to="/register">S'inscrire</b-nav-item>
        <b-nav-item to="/login">Se connecter</b-nav-item>

        <locale-switcher />
      </b-navbar-nav>
    </b-collapse>
  </b-navbar>
</template>

<script>
import Authenticated from '@/mixins/Authenticated';

import Dashboard from '@/assets/svg/dashboard.svg';
import Hand from '@/assets/svg/hand.svg';
import Help from '@/assets/svg/help.svg';
import Location from '@/assets/svg/location.svg';
import Logo from '@/assets/svg/logo.svg';
import Logout from '@/assets/svg/logout.svg';
import Profile from '@/assets/svg/profile.svg';
import SmallLogo from '@/assets/svg/small-logo.svg';

import AdminSidebar from '@/components/Admin/Sidebar.vue';
import LocaleSwitcher from '@/components/LocaleSwitcher.vue';

export default {
  name: 'Navbar',
  mixins: [Authenticated],
  components: {
    AdminSidebar,
    LocaleSwitcher,
    'svg-dashboard': Dashboard,
    'svg-hand': Hand,
    'svg-help': Help,
    'svg-location': Location,
    'svg-logo': Logo,
    'svg-logout': Logout,
    'svg-profile': Profile,
    'svg-small-logo': SmallLogo,
  },
  props: {
    title: {
      type: String,
      required: false,
      default: '',
    },
  },
  data() {
    return {
      toggleMenu: false,
    };
  },
};
</script>

<style lang="scss">
</style>
