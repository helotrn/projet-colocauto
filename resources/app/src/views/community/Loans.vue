<template>
  <div class="profile-loans" v-if="loaded">
    <template v-if="currentCommunityLoans.length > 0">
      <b-row no-gutters class="profile-loans__count">{{ context.total }} emprunts</b-row>
      <b-row class="profile-loans__loans">
        <b-col class="profile-loans__loans__loan" :lg="4" v-for="loan in currentCommunityLoans" :key="loan.id">
          <loan-info-box :loan="loan" :buttons="['view']" :user="user" with-steps />
        </b-col>
      </b-row>
      <div v-if="lastPage > 1">
          <b-pagination
            align="right"
            v-model="contextParams.page"
            :total-rows="total"
            :per-page="contextParams.per_page"
          />
      </div>
    </template>
    <div v-else>
      <p>Aucun emprunt.</p>
    </div>
  </div>
  <layout-loading v-else />
</template>

<script>
import LoanInfoBox from "@/components/Loan/InfoBox.vue";

import Authenticated from "@/mixins/Authenticated";
import DataRouteGuards from "@/mixins/DataRouteGuards";
import ListMixin from "@/mixins/ListMixin";
import UserMixin from "@/mixins/UserMixin";

export default {
  name: "ProfileLoans",
  mixins: [Authenticated, DataRouteGuards, ListMixin, UserMixin],
  components: { LoanInfoBox },
  computed: {
    currentCommunity() {
      return this.$store.state.communities.current
        ? this.$store.state.communities.current
        : this.user.main_community.id;
    },
    currentCommunityLoans() {
      return this.data.filter(e => e.community && e.community.id == this.currentCommunity)
    }
  },
  watch:{
    contextParams: {
      deep: true,
      handler(newp, oldp) {
        if( this.contextParams.community_id !=  this.$route.params.id ){
          this.contextParams.community_id = this.$route.params.id
          this.contextParams.page = 1
        }
        
      },
    },
  },
};
</script>

<style lang="scss">
.profile-loans__count {
  font-size: 22px;
  font-weight: bold;
  padding: 10px 0;
  margin-bottom: 1em;
  border-bottom: solid 1px $gray-400;
}
</style>
