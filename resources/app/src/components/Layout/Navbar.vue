<template>
  <b-navbar class="layout-navbar" toggleable="lg" variant="transparent" type="light">
    <b-collapse class="layout-navbar__collapse" is-nav>
      <b-navbar-nav class="mr-auto">
        <b-navbar-brand :to="isLoggedIn ? '/app' : '/'">
          <img src="/logo-colocauto.svg" alt="Coloc'Auto" width="180px" />
        </b-navbar-brand>

        <b-navbar-brand class="layout-navbar__separator d-none d-sm-block" v-if="title" />
        <b-navbar-brand class="navbar-brand--title d-none d-sm-block" v-if="title">
          {{ title }}
        </b-navbar-brand>
      </b-navbar-nav>
    </b-collapse>

    <b-navbar-brand :to="isLoggedIn ? '/app' : '/'">
      <img src="/logo-colocauto.svg" alt="Coloc'Auto" class="logo d-lg-none" />
    </b-navbar-brand>

    <b-navbar-toggle target="nav-collapse" v-if="!isLoggedIn" />

    <b-collapse id="nav-collapse" class="layout-navbar__collapse" is-nav>
      <div class="layout-navbar__collapse__illustration d-md-none" />

      <b-navbar-nav class="ml-auto" v-if="isLoggedIn">
        <b-nav-item to="/app" v-if="!(isGlobalAdmin || isCommunityAdmin) && isRegistered">
          <span class="nav-link__icon d-lg-none">
            <svg-dashboard />
          </span>
          <span class="nav-link__text">{{ $t("titles.dashboard") | capitalize }}</span>
        </b-nav-item>

        <b-nav-item to="/search/calendar" v-if="!(isGlobalAdmin || isCommunityAdmin) && canLoanVehicle">
          <span class="nav-link__icon d-lg-none">
            <svg-location />
          </span>
          <span class="nav-link__text">Emprunter un véhicule</span>
        </b-nav-item>

        <b-nav-item
          to="/profile"
          class="d-block d-lg-none"
          v-if="hasCompletedRegistration"
        >
          <span class="nav-link__icon">
            <svg-profile />
          </span>
          <span class="nav-link__text">Profil</span>
        </b-nav-item>

        <b-nav-item @click="logout" class="d-block d-lg-none">
          <span class="nav-link__icon">
            <svg-logout />
          </span>
          <span class="nav-link__text">Déconnexion</span>
        </b-nav-item>

        <b-nav-item-dropdown class="layout-navbar__admin" text="Admin" right v-if="isAdmin">
          <admin-sidebar :is-global-admin="isGlobalAdmin" :is-community-admin="isCommunityAdmin" />
        </b-nav-item-dropdown>

        <locale-switcher />

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
    
    <b-navbar-nav class="ml-auto" v-if="isLoggedIn">
        <b-nav-item-dropdown class="layout-navbar__dropdown d-lg-block" text="" right>
          <template v-slot:button-content>
            <user-avatar :user="user" />
          </template>
          <b-dropdown-item v-if="!(isGlobalAdmin || isCommunityAdmin) && hasCompletedRegistration">
            <user-avatar :user="user" />
            <span class="dropdown-container">
              <span class="username"> {{ user.name }}</span>
              <span class="username-title"></span>
            </span>
          </b-dropdown-item>
          <b-dropdown-divider v-if="!(isGlobalAdmin || isCommunityAdmin) && hasCompletedRegistration" />
          <b-dropdown-item
            to="/profile"
            v-if="hasCompletedRegistration"
          >
            <span class="nav-link__icon">
              <svg-profile />
            </span>
            <span class="nav-link__text">Mon compte</span>
          </b-dropdown-item>
          <b-dropdown-item to="/app" v-if="!(isGlobalAdmin || isCommunityAdmin) && hasCompletedRegistration">
            <span class="nav-link__icon">
              <svg-category />
            </span>
            <span class="nav-link__text">Tableau de bord</span>
          </b-dropdown-item>
          <b-dropdown-item to="/search/calendar?types=car" v-if="!(isGlobalAdmin || isCommunityAdmin) && canLoanVehicle">
            <span class="nav-link__icon">
              <svg-location />
            </span>
            <span class="nav-link__text">Emprunter un véhicule</span>
          </b-dropdown-item>
          <b-dropdown-item to="/wallet/expenses" v-if="!(isGlobalAdmin || isCommunityAdmin) && hasCompletedRegistration" :disabled="!user.main_community">
            <span class="nav-link__icon">
              <svg-euro />
            </span>
            <span class="nav-link__text">Portefeuille</span>
          </b-dropdown-item>
          <b-dropdown-item
            to="/community"
            v-if="!(isGlobalAdmin || isCommunityAdmin) && hasCompletedRegistration && user.main_community"
          >
            <span class="nav-link__icon">
              <svg-vector />
            </span>
            <span class="nav-link__text">Espace référent</span>
          </b-dropdown-item>
          <b-dropdown-item to="/community/loans" v-if="!(isGlobalAdmin || isCommunityAdmin) && hasCompletedRegistration" :disabled="!user.main_community">
            <span class="nav-link__icon">
              <b-icon icon="list-check" />
            </span>
            <span class="nav-link__text">Historique des emprunts</span>
          </b-dropdown-item>
          <b-dropdown-divider />
          <b-dropdown-item @click="logout">
            <span class="nav-link__icon">
              <svg-logout />
            </span>
            <span class="nav-link__text">Déconnexion</span>
          </b-dropdown-item>
        </b-nav-item-dropdown>
    </b-navbar-nav>
  </b-navbar>
</template>

<script>
import Category from "@/assets/svg/category.svg";
import Dashboard from "@/assets/svg/dashboard.svg";
import Hand from "@/assets/svg/hand.svg";
import Help from "@/assets/svg/help.svg";
import Location from "@/assets/svg/location.svg";
import Login from "@/assets/svg/login.svg";
import Logout from "@/assets/svg/logout.svg";
import Profile from "@/assets/svg/profile.svg";
import Register from "@/assets/svg/register.svg";
import Vector from "@/assets/svg/vector.svg";
import Euro from "@/assets/icons/euro.svg";

import AdminSidebar from "@/components/Admin/Sidebar.vue";
import LocaleSwitcher from "@/components/LocaleSwitcher.vue";
import UserAvatar from "@/components/User/Avatar.vue";

import UserMixin from "@/mixins/UserMixin";

export default {
  name: "Navbar",
  mixins: [UserMixin],
  components: {
    AdminSidebar,
    LocaleSwitcher,
    UserAvatar,
    "svg-category": Category,
    "svg-dashboard": Dashboard,
    "svg-hand": Hand,
    "svg-help": Help,
    "svg-location": Location,
    "svg-login": Login,
    "svg-logout": Logout,
    "svg-profile": Profile,
    "svg-register": Register,
    "svg-vector": Vector,
    "svg-euro": Euro,
  },
  props: {
    title: {
      type: String,
      required: false,
      default: "",
    },
  },
  methods: {
    async logout() {
      this.$router.push("/");

      await this.$store.dispatch("logout");

      this.$store.commit("addNotification", {
        content: "Vous n'êtes plus connecté à Coloc'Auto. À bientôt!",
        title: "Déconnexion réussie.",
        variant: "success",
        type: "logout",
      });
    },
  },
};
</script>

<style lang="scss" scoped>
.layout-navbar__dropdown::v-deep .nav-link.dropdown-toggle {
  border-bottom: none;
}
.layout-navbar__collapse + .navbar-nav::v-deep .dropdown-menu-right {
  position: absolute;
}
</style>
