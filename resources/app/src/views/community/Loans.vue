<template>
  <div class="profile-loans" v-if="loaded">
    <div class="profile-loans__loans" v-if="currentCommunityLoans.length > 0">
      <div class="profile-loans__loans__loan" v-for="loan in currentCommunityLoans" :key="loan.id">
        <loan-info-box :loan="loan" :buttons="['view']" :user="user" with-steps />
      </div>
      <div v-if="lastPage > 1">
          <b-pagination
            align="right"
            v-model="contextParams.page"
            :total-rows="total"
            :per-page="contextParams.per_page"
          />
      </div>
    </div>
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
  mounted(){
    this.contextParams.page = 1;
    this.contextParams['community.id'] = this.currentCommunity;
  },
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
    currentCommunity(){
      // reload data when community change
      this.contextParams.page = 1;
      this.contextParams['community.id'] = this.currentCommunity
      this.loadListData()
    }
  },
};
</script>

<style lang="scss"></style>
