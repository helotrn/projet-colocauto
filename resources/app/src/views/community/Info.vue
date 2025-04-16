<template>
  <b-container v-if="item" fluid tag="section">
    <b-row v-if="!user.main_community" align-v="center">
      <b-col lg="2" md="3" sm="4" cols="6">
        <svg-discussion width="100px" class="p-2"/>
      </b-col>
      <b-col>
        <p class="mb-0">
          Sur Coloc'Auto, des groupes de personnes se partagent l'usage d'un ou
          plusieurs véhicules. Pour cela, il se regroupent au sein de communautés.
        </p>
      </b-col>
    </b-row>

    <h1 v-if="!user.main_community">Créer votre première communauté</h1>
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
  mixins: [DataRouteGuards, UserMixin, FormMixin],
  components: {SvgDiscussion, CommunityForm},
  computed: {
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
      } else {
        vm.$store.dispatch(`communities/loadEmpty`);
      }
    });
  },
}
</script>
