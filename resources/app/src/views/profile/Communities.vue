<template>
  <div class="profile-communities" v-if="item && routeDataLoaded">
    <div class="profile-communities__communities" v-if="item.communities.length > 0">
      <b-card
        class="profile-communities__communities__community"
        v-for="community in item.communities"
        :key="community.id"
      >
        <div class="profile-communities__headers">
          <h4>Afin que LocoMotion reste un service sécuritaire entre voisin-e-s,</h4>
          <h3>Veuillez téléverser une preuve de résidence pour {{ community.name }}</h3>
        </div>

        <b-card-body class="profile-communities__community_proof">
          <community-proof-form :community="community" @submit="submit" :loading="loading" />
        </b-card-body>
      </b-card>
    </div>

    <b-card class="profile-communities__nocommunities" v-else>
      <div>
        <h3>Vous n'êtes pas encore inscrit au sein d'un quartier soutenu par LocoMotion.</h3>
      </div>
      <b-card-body>
        <p>
          Il se peut que LocoMotion ne soit pas encore ouvert dans votre quartier ou que votre
          adresse n'est pas à jour.
        </p>
        <b-button variant="primary" to="/profile/locomotion"> Modifier mon adresse </b-button>
      </b-card-body>
    </b-card>
  </div>
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
  &__nocommunities {
    .btn {
      margin-left: 0;
    }
  }
}
</style>
