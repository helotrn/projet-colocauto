<template>
  <b-container v-if="item" fluid tag="section">
    <div v-if="!user.main_community || isFirstCommunity" class="note">
      <svg-discussion width="100px" class="p-2"/>
      <p>Sur Coloc'Auto, des groupes de personnes se partagent l'usage d'un ou
      plusieurs véhicules. Pour cela, il se regroupent au sein de communautés.</p>
    </div>

    <h1 v-if="!user.main_community || isFirstCommunity">Créer votre première communauté</h1>
    <community-form
      :loading="loading"
      :community="item"
      :form="form"
      @reset="reset"
      :changed="changed"
      @submit="submit"
    />

  </b-container>
  <layout-loading v-else />
</template>

<script>
import DataRouteGuards from "@/mixins/DataRouteGuards";
import UserMixin from "@/mixins/UserMixin";
import FormMixin from "@/mixins/FormMixin";
import CommunityForm from "@/components/Community/CommunityForm.vue";
import SvgDiscussion from "@/assets/svg/discussion.svg";

export default {
  name: "CommunityInfo",
  data() {
    return ({
      isFirstCommunity: false
    })
  },
  mixins: [DataRouteGuards, UserMixin, FormMixin],
  components: {SvgDiscussion, CommunityForm},
  computed: {
    // item is loaded manually in beforeRouteEnter
    skipLoadItem() {
      return true;
    },
    currentCommunityId() {
      return this.$store.state.communities.current
        ? this.$store.state.communities.current
        : this.user.main_community?.id;
    },
  },
  beforeRouteEnter(to, from, next) {
    next((vm) => {
      if( vm.currentCommunityId ){
        vm.$store.dispatch(`communities/retrieveOne`, {
          id: vm.currentCommunityId,
          params: vm.params,
        });
        // load invitations
        vm.$store.dispatch('invitations/retrieve', {
          community_id: vm.currentCommunityId,
        });
        vm.$store.dispatch('invitations/loadEmpty').then(() => {
          vm.$store.state.invitations.item.community_id = vm.currentCommunityId;
          vm.$store.state.invitations.item.community = vm.currentCommunityId;
        })
      } else {
        vm.$store.dispatch(`communities/loadEmpty`);
      }
    });
  },
  methods: {
    async afterSubmit() {
      // reload user to get the main community
      if(!this.currentCommunityId) this.$store.dispatch("loadUser");

      await this.$store.dispatch('invitations/loadEmpty');
      this.$store.state.invitations.item.community_id = this.item.id;
      this.$store.state.invitations.item.community = this.item;
      await this.$store.dispatch('invitations/retrieve', {community_id: this.item.id});
    },
  },
  beforeMount(){
    if( !this.currentCommunityId ) this.isFirstCommunity = true;
  }
}
</script>
