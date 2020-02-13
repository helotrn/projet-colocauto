<template>
  <b-navbar
    class="layout-navbar"
    toggleable="lg"
    variant="transparent"
    type="light">
    <div class="layout-navbar__brand-wrapper">
      <b-navbar-brand to="/">
        <svg-logo class="layout-navbar__logo" />
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
        <b-nav-item to="/app">
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
          <span class="nav-link__text">Communauté</span>
        </b-nav-item>

        <b-nav-item to="/register" v-if="!isAdmin && !hasCompletedRegistration">
          <span class="nav-link__icon d-lg-none">
            <svg-hand />
          </span>
          <span class="nav-link__text">Inscription</span>
        </b-nav-item>

        <b-nav-item to="/profile" class="d-block d-lg-none" >
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
          <admin-menu />
        </b-nav-item-dropdown>

        <locale-switcher />

        <b-nav-item-dropdown class="layout-navbar__dropdown d-none d-lg-block" text="" right>
          <template v-slot:button-content>
            <b-badge pill variant="locomotion" class="layout-navbar__dropdown__icon">
              <svg-profile />
            </b-badge>
          </template>
          <b-dropdown-item to="/profile">Profil</b-dropdown-item>
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

import Logo from '@/assets/svg/logo.svg';
import Dashboard from '@/assets/svg/dashboard.svg';
import Location from '@/assets/svg/location.svg';
import Hand from '@/assets/svg/hand.svg';
import Profile from '@/assets/svg/profile.svg';
import Help from '@/assets/svg/help.svg';
import Logout from '@/assets/svg/logout.svg';

import AdminMenu from '@/components/Admin/Menu.vue';
import LocaleSwitcher from '@/components/LocaleSwitcher.vue';

export default {
  name: 'Navbar',
  mixins: [Authenticated],
  components: {
    AdminMenu,
    LocaleSwitcher,
    'svg-logo': Logo,
    'svg-dashboard': Dashboard,
    'svg-location': Location,
    'svg-hand': Hand,
    'svg-profile': Profile,
    'svg-help': Help,
    'svg-logout': Logout,
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
@import "@/assets/scss/tools";

.navbar.layout-navbar {
  flex-direction: row-reverse;
  justify-content: flex-end;

  @include media-breakpoint-up(lg) {
    flex-direction: row;
    justify-content: space-between;
    padding-left: $grid-gutter-width / 2;
    padding-right: $grid-gutter-width / 2;
  }

  .layout-navbar {
    &__collapse {
      @include media-breakpoint-down(md) {
        position: absolute;
        top: #{$layout-navbar-height-mobile - 13};
        left: 0;
        width: 100vw;
        z-index: 1000;
        background: $white;
      }

      &__illustration {
        background: url("/mobile-menu-illustration.png") center no-repeat;
        background-size: cover;
        height: 44vw;
        margin-bottom: 52px;

        svg {
          width: 100vw;
        }
      }
    }
  }

  .nav-link__icon path {
    fill: $locomotion-grey;
  }

  .layout-navbar__logo {
    width: 162px;
  }

  .layout-navbar__brand-wrapper {
    display: flex;
    align-items: flex-end;
  }

  .layout-navbar__separator {
    width: 1px;
    height: 36px;
    background-color: $locomotion-green;
    position: relative;
    top: 5px;
  }

  .navbar-brand {
    font-size: 1rem;

    @include media-breakpoint-up(lg) {
      font-size: 1.6rem;
    }

    &--title {
      padding-bottom: 0;
      position: relative;
      top: 3px;
    }
  }

  .nav-item {
    margin-left: 10px;
  }

  .nav-link {
    font-size: 24px;
    padding: 20px $grid-gutter-width / 2;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;

    @include media-breakpoint-up(lg) {
      padding: 0.5rem 1.5rem;
      font-size: $base-font-size;
      border-bottom: 0;
    }
  }

  .nav-link__icon {
    width: 40px;
    display: flex;
    align-items: center;
  }

  .navbar-nav .nav-link.router-link-exact-active,
  .navbar-nav .nav-link.router-link-active,
  .navbar-nav .nav-link:hover {
    color: $locomotion-green;
  }

  .router-link-exact-active path,
  .nav-link:hover path {
    fill: $locomotion-green;
  }

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

  .navbar-toggle-wrapper {
    margin-left: -12px;
    margin-right: 27px;
    padding-left: $grid-gutter-width / 2;
  }

  .navbar-toggler {
    border: 0;
    display: flex;
    justify-content: center;
    align-items: center;
  }

  .navbar-toggler-icon {
    width: 22px;
    height: 15px;
    background-image: url("/menu.svg");
  }

  .navbar-toggle-wrapper--active .navbar-toggler-icon {
    background-image: url("/menu-open.svg");
  }

  .badge-pill path,
  .dropdown-toggle:hover path {
    fill: $light;
  }
}
</style>
