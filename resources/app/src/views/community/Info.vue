<template>
  <b-container v-if="item && !loading" fluid tag="section">
    <div v-if="!user.main_community || isFirstCommunity" class="note">
      <svg-discussion width="100px" class="p-2"/>
      <p>Sur Coloc'Auto, des groupes de personnes se partagent l'usage d'un ou
      plusieurs véhicules. Pour cela, ils se regroupent au sein de communautés.</p>
    </div>

    <h1 v-if="!user.main_community || isFirstCommunity">Créer votre première communauté</h1>
    <h1 v-else-if="id == 'new'">Créer une autre communauté</h1>
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
import Authenticated from "@/mixins/Authenticated";
import UserMixin from "@/mixins/UserMixin";
import FormMixin from "@/mixins/FormMixin";
import CommunityForm from "@/components/Community/CommunityForm.vue";
import SvgDiscussion from "@/assets/svg/discussion.svg";

export default {
  name: "CommunityInfo",
  data() {
    return ({
      isFirstCommunity: false,
      isNew: false,
    })
  },
  mixins: [Authenticated, DataRouteGuards, UserMixin, FormMixin],
  components: {SvgDiscussion, CommunityForm},
  computed: {
    // item is loaded manually in beforeRouteEnter
    skipLoadItem() {
      return true;
    },
  },
  beforeRouteEnter(to, from, next) {
    next((vm) => {
      if( vm.id != 'new' && vm.hasCommunity ){
        if( vm.id == vm.currentCommunity ) {
          vm.loadCurrentCommunity();
        }
      } else {
        vm.$store.dispatch(`communities/loadEmpty`);
      }
    });
  },
  methods: {
    async afterSubmit() {
      if( this.isNew ){
        await this.$store.dispatch("loadUser");
        this.$store.dispatch("communities/setCurrent", { communityId: this.id })
        this.isNew = false
        this.isFirstCommunity = false;
        return;
      }

      // reload user to get the main community
      if(!this.hasCommunity) {
        await this.$store.dispatch("loadUser");
        this.isFirstCommunity = false;
      }

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
      this.$store.dispatch('invitations/loadEmpty').then(() => {
        this.$store.state.invitations.item.community_id = this.currentCommunity;
        this.$store.state.invitations.item.community = this.currentCommunity;
      })
      this.$store.dispatch('invitations/retrieve', {
        community_id: this.currentCommunity,
      });
    },
  },
  beforeMount(){
    if( !this.hasCommunity ) this.isFirstCommunity = true;
    if( this.id == 'new' ) this.isNew = true;
  },
  watch: {
    currentCommunity(current, oldc) {
      if( !this.initialLoading ) {
        this.loadCurrentCommunity()
      }
    },
    id() {
      if(this.id == 'new') {
        this.isNew = true
        this.$store.dispatch(`communities/loadEmpty`);
      }
    },
  },
}
</script>
