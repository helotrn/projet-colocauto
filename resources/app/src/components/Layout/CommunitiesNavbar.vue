<template>
  <b-container v-if="isLoggedIn && !isAdmin && user.communities.length > 1" class="pt-5">
    <h3>Vos communautés</h3>
    <b-tabs>
      <template #tabs-end>
        <b-nav-item
          v-for="community in user.communities"
          :key="community.id"
          :active="isCurrentCommunity(community.id)"
          :disabled="isCommunityForcedByRoute && !isCurrentCommunity(community.id)"
          @click="changeCommunity(community.id)"
        >
          <span class="nav-link__text">{{ community.name }}</span>
        </b-nav-item>
      </template>
    </b-tabs>
  </b-container>
</template>

<script>
import UserMixin from "@/mixins/UserMixin";

export default {
  name: "CommunitiesNavbar",
  mixins: [UserMixin],
  methods: {
    isCurrentCommunity(communityId) {
      return this.$store.state.communities.current == communityId
      || (this.$store.state.communities.current === null && this.user.main_community.id == communityId)
    },
    changeCommunity(communityId) {
      this.$store.dispatch("communities/setCurrent", { communityId })
      this.$store.dispatch("communities/retrieveOne", {
        id: communityId,
        params: this.$route.meta.params,
      })
      this.$store.dispatch("dashboard/loadBalance", { community: {id:communityId} })
      this.$store.dispatch("dashboard/loadMembers", { user: this.user })
    },
  },
  computed: {
    isCommunityForcedByRoute() {
      return ['single-loan', 'single-loanable', 'single-expense', 'single-refund'].includes(this.$route.name)
    },
  },
}
</script>

<style lang="scss" scoped>
  .tabs::v-deep {
    .tab-content {
      display: none;
    }
    .nav-tabs {
      border-radius: 0.625rem 0.625rem 0.625rem 0;
    }
  }
</style>
