<template>
  <b-container v-if="isLoggedIn && !isAdmin && !isRegisterRoute" class="pt-5">
    <b-row no-gutters class="d-flex justify-content-between">
      <h3>Vos communautés</h3>
      <b-btn
        v-if="canCreateCommunity"
        variant="primary"
        to="/community/new"
        class="btn-rounded d-md-none"
      >
        <span class="sr-only">Ajouter</span>
        <plus-icon width="18"/>
      </b-btn>
    </b-row>
    <b-tabs>
      <template #tabs-end>
        <b-nav-item
          v-for="community in user.communities"
          :key="community.id"
          :active="isCurrentCommunity(community.id)"
          :disabled="(isCommunityForcedByRoute || isNewCommunityRoute) && !isCurrentCommunity(community.id)"
          @click="changeCommunity(community.id)"
        >
          <span class="nav-link__text">{{ community.name }}</span>
        </b-nav-item>
        <b-btn
          v-if="canCreateCommunity"
          variant="outline-primary"
          to="/community/new"
          class="d-none d-md-block"
        >
          Créer une communauté
        </b-btn>
      </template>
    </b-tabs>
  </b-container>
</template>

<script>
import UserMixin from "@/mixins/UserMixin";
import PlusIcon from "@/assets/icons/plus.svg";

export default {
  name: "CommunitiesNavbar",
  mixins: [UserMixin],
  components: {PlusIcon},
  methods: {
    isCurrentCommunity(communityId) {
      return this.$store.state.communities.current == communityId
      || (this.$store.state.communities.current === null && this.user.main_community.id == communityId)
    },
    async changeCommunity(communityId) {
      const matchedPath = this.$route.path.match(/community\/([0-9]*)(\/.*)?/)
      if( matchedPath ){
        this.$router.push(this.$route.path.replace(matchedPath[1], communityId))
        setTimeout(() => this.$store.dispatch("communities/setCurrent", { communityId }), 300);
      } else {
        this.$store.dispatch("communities/retrieveOne", {
          id: communityId,
          params: this.$route.meta.params,
        })

        // if dashboard
        this.$store.dispatch("dashboard/loadBalance", { community: {id:communityId} })
        this.$store.dispatch("dashboard/loadMembers", { user: this.user })

        this.$store.dispatch("communities/setCurrent", { communityId })
      }
    },
  },
  computed: {
    isCommunityForcedByRoute() {
      return ['single-loan', 'community-single-loanable', 'single-expense', 'single-refund'].includes(this.$route.name)
    },
    isNewCommunityRoute(){
      return this.$route.name === 'community-info' && this.$store.state.communities.item && !this.$store.state.communities.item.id;
    },
    isRegisterRoute(){
      return ['register-intro', 'register-profile', 'register-community', 'invitation-to-community', 'register-loanable'].includes(this.$route.name)
    },
  },
}
</script>

<style lang="scss" scoped>
  @import "~bootstrap/scss/mixins/breakpoints";
  .tabs {
    position: relative;
    .btn {
      position: absolute;
      right: 20px;
      top: 10px;
      z-index: 10;
    }
  }
  .tabs::v-deep {
    .tab-content {
      display: none;
    }
    .nav-tabs {
      border-radius: 0.625rem 0.625rem 0.625rem 0;
      flex-wrap: nowrap;
      overflow: scroll hidden;
      @include media-breakpoint-up(md) {
        padding-right: 300px;
      }
      .nav-link {
        text-wrap: nowrap;
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
      }
      @include media-breakpoint-up(md) {
        &:after {
          content: '';
          position: absolute;
          background: linear-gradient(279deg, white 0%, white 80%, transparent);
          height: 100%;
          width: 300px;
          right: 0;
          z-index: 0;
        }
      }
    }
  }
</style>
