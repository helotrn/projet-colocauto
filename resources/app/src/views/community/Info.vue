<template>
  <b-container v-if="item && !loading" fluid tag="section">
    <div v-if="!user.main_community || isFirstCommunity" class="note">
      <svg-discussion width="100px" class="p-2"/>
      <p>Sur Coloc'Auto, des groupes de personnes se partagent l'usage d'un ou
      plusieurs véhicules. Pour cela, ils se regroupent au sein de communautés.</p>
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
  },
  beforeRouteEnter(to, from, next) {
    next((vm) => {
      if( vm.hasCommunity ){
        vm.loadCurrentCommunity();
      } else {
        vm.$store.dispatch(`communities/loadEmpty`);
      }
    });
  },
  methods: {
    async afterSubmit() {
      // reload user to get the main community
      if(!this.hasCommunity) this.$store.dispatch("loadUser");

      await this.$store.dispatch('invitations/loadEmpty');
      this.$store.state.invitations.item.community_id = this.item.id;
      this.$store.state.invitations.item.community = this.item;
      await this.$store.dispatch('invitations/retrieve', {community_id: this.item.id});
    },
    loadCurrentCommunity(){
      this.$store.dispatch(`communities/retrieveOne`, {
        id: this.currentCommunity,
        params: this.params,
      });
      // load invitations
      this.$store.dispatch('invitations/retrieve', {
        community_id: this.currentCommunity,
      });
      this.$store.dispatch('invitations/loadEmpty').then(() => {
        this.$store.state.invitations.item.community_id = this.currentCommunity;
        this.$store.state.invitations.item.community = this.currentCommunity;
      })
    },
  },
  beforeMount(){
    if( !this.hasCommunity ) this.isFirstCommunity = true;
  },
  watch: {
    currentCommunity() {
      this.loadCurrentCommunity()
    }
  },
}
</script>
