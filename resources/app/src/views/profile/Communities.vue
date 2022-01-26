<template>
  <div class="profile-communities" v-if="item && routeDataLoaded">
    <div class="profile-communities__communities" v-if="item.communities.length > 0">
      <b-card
        class="profile-communities__communities__community"
        v-for="community in item.communities"
        :key="community.id"
      >
        <div class="profile-communities__headers">
          <h4>Afin que LocoMotion reste un service entre voisin.e.s sécuritaire,</h4>
          <h2>Veuillez téléverser une preuve de résidence pour {{ community.name }}</h2>
        </div>

        <b-card-body>
          <community-proof-form :community="community" @submit="submit" />
        </b-card-body>
      </b-card>
    </div>
    <div v-else>
      <p>
        Vous n'êtes membre d'aucun voisinage?
        <router-link to="/register/map">Cliquez ici</router-link> pour rejoindre un premier
        voisinage!
      </p>
    </div>
  </div>
  <layout-loading v-else />
</template>

<script>
import CommunityProofForm from "@/components/Community/ProofForm.vue";

import DataRouteGuards from "@/mixins/DataRouteGuards";
import FormMixin from "@/mixins/FormMixin";

export default {
  name: "ProfileCommunities",
  mixins: [DataRouteGuards, FormMixin],
  components: { CommunityProofForm },
  props: {
    id: {
      type: String,
      required: false,
      default: "me",
    },
  },
};
</script>

<style lang="scss">
.profile-communities {
  &__headers {
    padding-left: 15px;
    h4 {
      color: grey;
      font-size: 16px;
    }
  }
  &__communities__community {
    margin-bottom: 20px;
  }
}
</style>
