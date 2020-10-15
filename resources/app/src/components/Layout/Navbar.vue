<template>
  <b-navbar
    class="layout-navbar"
    toggleable="lg"
    variant="transparent"
    type="light">
    <div class="layout-navbar__brand-wrapper">
      <b-navbar-brand :to="isLoggedIn ? '/app' : '/'">
        <img src="/logo.png" alt="Locomotion Beta">
      </b-navbar-brand>

      <b-navbar-brand class="layout-navbar__separator d-none d-sm-block" v-if="title" />
      <b-navbar-brand class="navbar-brand--title d-none d-sm-block" v-if="title">
        {{ title }}
      </b-navbar-brand>
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

    <b-collapse id="nav-collapse" class="layout-navbar__collapse" is-nav
      v-model="toggleMenu">
      <div class="layout-navbar__collapse__illustration d-md-none" />

      <b-navbar-nav class="ml-auto" v-if="isLoggedIn">
        <b-nav-item to="/app" v-if="!isGlobalAdmin && isRegistered">
          <span class="nav-link__icon d-lg-none">
            <svg-dashboard />
          </span>
          <span class="nav-link__text">{{ $t('titles.dashboard') | capitalize }}</span>
        </b-nav-item>

        <b-nav-item to="/community/map" v-if="!isGlobalAdmin && canLoanVehicle">
          <span class="nav-link__icon d-lg-none">
            <svg-location />
          </span>
          <span class="nav-link__text">Réserver un véhicule</span>
        </b-nav-item>

        <b-nav-item to="/community" v-if="!isGlobalAdmin && hasCommunity">
          <span class="nav-link__icon d-lg-none">
            <svg-hand />
          </span>
          <span class="nav-link__text">
            <span v-if="user.communities[0].type === 'borough'">Quartier</span>
            <span v-else>Voisinage</span>
          </span>
        </b-nav-item>

        <b-nav-item to="/register" v-if="!isGlobalAdmin && !hasCompletedRegistration">
          <span class="nav-link__icon d-lg-none">
            <svg-hand />
          </span>
          <span class="nav-link__text">Inscription</span>
        </b-nav-item>

        <b-nav-item to="/profile/locomotion" class="d-block d-lg-none"
          v-if="!isGlobalAdmin && hasCompletedRegistration">
          <span class="nav-link__icon">
            <svg-profile />
          </span>
          <span class="nav-link__text">Profil</span>
        </b-nav-item>

        <b-nav-item to="/faq" class="d-block d-lg-none" >
          <span class="nav-link__icon">
            <svg-help />
          </span>
          <span class="nav-link__text">FAQ</span>
        </b-nav-item>

        <b-nav-item @click="logout" class="d-block d-lg-none">
          <span class="nav-link__icon">
            <svg-logout />
          </span>
          <span class="nav-link__text">Déconnexion</span>
        </b-nav-item>

        <b-nav-item-dropdown class="layout-navbar__admin" text="Admin" right v-if="isAdmin">
          <admin-sidebar :is-global-admin="isGlobalAdmin" />
        </b-nav-item-dropdown>

        <locale-switcher />

        <b-nav-item-dropdown class="layout-navbar__dropdown d-none d-lg-block" text="" right>
          <template v-slot:button-content>
            <b-badge pill variant="locomotion" class="layout-navbar__dropdown__icon">
              <svg-profile />
            </b-badge>
          </template>
          <b-dropdown-item to="/profile/locomotion"
            v-if="!isGlobalAdmin && hasCompletedRegistration">
            Profil
          </b-dropdown-item>
          <b-dropdown-item to="/faq">FAQ</b-dropdown-item>
          <b-dropdown-divider />
          <b-dropdown-item @click="logout">Déconnexion</b-dropdown-item>
        </b-nav-item-dropdown>
      </b-navbar-nav>

      <b-navbar-nav class="ml-auto" v-else>
        <b-nav-item to="/register">
          <span class="nav-link__icon d-lg-none">
            <svg-register />
          </span>
          <span class="nav-link__text">S'inscrire</span>
        </b-nav-item>
        <b-nav-item to="/login">
          <span class="nav-link__icon d-lg-none">
            <svg-login />
          </span>
          <span class="nav-link__text">Se connecter</span>
        </b-nav-item>

        <locale-switcher />
      </b-navbar-nav>

      <div class="layout-navbar__collapse__buffer" />
    </b-collapse>
  </b-navbar>
</template>

<script>
import Dashboard from '@/assets/svg/dashboard.svg';
import Hand from '@/assets/svg/hand.svg';
import Help from '@/assets/svg/help.svg';
import Location from '@/assets/svg/location.svg';
import Login from '@/assets/svg/login.svg';
import Logout from '@/assets/svg/logout.svg';
import Profile from '@/assets/svg/profile.svg';
import Register from '@/assets/svg/register.svg';

import AdminSidebar from '@/components/Admin/Sidebar.vue';
import LocaleSwitcher from '@/components/LocaleSwitcher.vue';

import UserMixin from '@/mixins/UserMixin';

export default {
  name: 'Navbar',
  mixins: [UserMixin],
  components: {
    AdminSidebar,
    LocaleSwitcher,
    'svg-dashboard': Dashboard,
    'svg-hand': Hand,
    'svg-help': Help,
    'svg-location': Location,
    'svg-login': Login,
    'svg-logout': Logout,
    'svg-profile': Profile,
    'svg-register': Register,
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
  methods: {
    async logout() {
      await this.$store.dispatch('logout');

      this.$store.commit('addNotification', {
        content: "Vous n'êtes plus connecté à LocoMotion. À bientôt!",
        title: 'Déconnexion réussie.',
        variant: 'success',
        type: 'logout',
      });

      this.$router.push('/');
    },
  },
  watch: {
    toggleMenu(val) {
      const { body: { style } } = document;
      if (val) {
        style.overflow = 'hidden';
        style.height = '100vh';
      } else {
        style.overflow = 'auto';
        style.height = 'auto';
      }
    },
  },
};
</script>

<style lang="scss">
</style>
