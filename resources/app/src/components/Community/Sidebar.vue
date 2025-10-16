<template>
  <div class="community-sidebar">
    <ul v-if="user">
      <b-nav-item :to="`/community/${currentCommunity}`">
        Informations
      </b-nav-item>
      <b-nav-item :to="`/community/${currentCommunity}/members`">
        Membres
      </b-nav-item>
      <b-nav-item :to="`/community/${currentCommunity}/loanables`">
        Véhicules
      </b-nav-item>
      <b-nav-item :to="`/community/${currentCommunity}/loans`">
        Historique d'emprunts
      </b-nav-item>
    </ul>

    <info-link-block title="Contactez-nous" to="mailto:soutien@colocauto.org">
      <svg-contact class="m-2"/>
    </info-link-block>

  </div>
</template>

<script>
import UserMixin from "@/mixins/UserMixin";
import InfoLinkBlock from "@/components/Dashboard/InfoLinkBlock.vue";
import Contact from "@/assets/svg/contact.svg";

export default {
  name: "CommunitySidebar",
  mixins: [UserMixin],
  components: {
    InfoLinkBlock,
    "svg-contact": Contact,
  },
};
</script>

<style lang="scss">
.community-sidebar {
  hr {
    margin: 30px 0;
  }

  ul {
    list-style: none;
    padding: 1em;
    margin: 0 0 1em 0;
    font-size: 20px;
    background: white;
    border-radius: .5em;

    .nav-item .nav-link {
      color: $black;
      padding-left: 0;

      &.disabled {
        color: $grey;
      }

      &.router-link-exact-active {
        font-weight: bold;
      }
      &:hover {
        text-decoration: underline;
      }
    }
  }
}
</style>
