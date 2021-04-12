<template>
  <div class="profile-communities" v-if="item && routeDataLoaded">
    <div class="profile-communities__communities" v-if="item.communities.length > 0">
      <b-card class="profile-communities__communities__community"
        v-for="community in item.communities" :key="community.id">
        <b-card-header header-tag="h3">{{ community.name }}</b-card-header>
        <b-card-body>
          <community-proof-form :community="community" @submit="submit" />
        </b-card-body>
      </b-card>
    </div>
    <div v-else>
      <p>
        Vous n'êtes membre d'aucun voisinage?
        <router-link to="/register/map">Cliquez ici</router-link> pour rejoindre un
        premier voisinage!
      </p>
    </div>
  </div>
  <layout-loading v-else />
</template>

<script>
import CommunityProofForm from '@/components/Community/ProofForm.vue';

import DataRouteGuards from '@/mixins/DataRouteGuards';
import FormMixin from '@/mixins/FormMixin';

export default {
  name: 'ProfileCommunities',
  mixins: [DataRouteGuards, FormMixin],
  components: { CommunityProofForm },
  props: {
    id: {
      type: String,
      required: false,
      default: 'me',
    },
  },
};
</script>

<style lang="scss">
.profile-communities {
  &__communities__community {
    margin-bottom: 20px;
  }
}
</style>
