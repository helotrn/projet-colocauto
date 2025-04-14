<template>
  <div class="community-sidebar">
    <ul v-if="user">
      <b-nav-item to="/community">
        Informations
      </b-nav-item>
      <b-nav-item to="/community/members">
        Membres
      </b-nav-item>
      <b-nav-item to="/community/loanables">
        Véhicules
      </b-nav-item>
      <b-nav-item to="/community/loans">
        Historique d'emprunts
      </b-nav-item>
    </ul>

    <hr />
  </div>
</template>

<script>
import UserMixin from "@/mixins/UserMixin";

export default {
  name: "CommunitySidebar",
  mixins: [UserMixin],
};
</script>

<style lang="scss">
.community-sidebar {
  hr {
    margin: 30px 0;
  }

  ul {
    list-style: none;
    padding: 0;
    margin: 0;

    .nav-item .nav-link {
      color: $black;
      padding-left: 0;

      &.disabled {
        color: $grey;
      }

      &.router-link-exact-active,
      &:hover {
        color: $locomotion-green;
      }
    }
  }
}
</style>
