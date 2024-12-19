<template>
  <b-container v-if="user.communities.length > 1">
    <b-navbar>
      <b-navbar-nav align="start" v-if="isLoggedIn">
        <b-nav-item
          v-for="community in user.communities"
          :key="community.id"
          :active="isCurrentCommunity(community.id)"
          :disabled="isCommunityForcedByRoute && !isCurrentCommunity(community.id)"
          @click="changeCommunity(community.id)"
        >
          <span class="nav-link__text">{{ community.name }} ({{community.id}})</span>
        </b-nav-item>
      </b-navbar-nav>
    </b-navbar>
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
