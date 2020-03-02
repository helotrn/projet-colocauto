<template>
  <div class="profile-communities">
    <div class="profile-communities__communities" v-if="item">
      <div class="profile-communities__communities__community"
        v-for="community in item.communities" :key="community.id">
        <h3>{{ community.name }}</h3>
        <community-proof-form :community="community" @submit="submit" />
      </div>
    </div>
  </div>
</template>

<script>
import CommunityProofForm from '@/components/Community/ProofForm.vue';

import FormMixin from '@/mixins/FormMixin';

export default {
  name: 'ProfileCommunities',
  mixins: [FormMixin],
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
</style>
