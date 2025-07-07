<template>
  <layout-page name="community" padded>
    <b-row>
      <b-col md="4" lg="3" v-if="showSidebar">
        <community-sidebar  />
      </b-col>

      <b-col :md="showSidebar ? 8 : 12" :lg="showSidebar ? 9 : 12">
        <router-view />
      </b-col>
    </b-row>
  </layout-page>
</template>


<script>
import CommunitySidebar from "@/components/Community/Sidebar.vue";

import Authenticated from "@/mixins/Authenticated";
import UserMixin from "@/mixins/UserMixin";
import DataRouteGuards from "@/mixins/DataRouteGuards";

export default {
  name: "Community",
  mixins: [Authenticated, UserMixin],
  components: {
    CommunitySidebar,
  },
  computed: {
    isNewCommunityRoute(){
      return this.$route.name === 'community-info' && this.$store.state.communities.item && !this.$store.state.communities.item.id;
    },
    showSidebar() {
      return this.user.main_community && !this.isNewCommunityRoute
    }
  },
  beforeRouteEnter(to, from, next) {
    next((vm) => {
      const community_id = to.name == 'community-single-loanable' ? to.params.cid : to.params.id;
      if( !community_id && vm.currentCommunity ) {
        return vm.$router.replace(`/community/${vm.currentCommunity}`);
      } else if( community_id && community_id != vm.currentCommunity && community_id != 'new' ) {
        vm.$store.dispatch("communities/setCurrent", { communityId: community_id })
      } else if(!community_id) {
        return vm.$router.replace('/community/new');
      }
    })
  },
};
</script>
