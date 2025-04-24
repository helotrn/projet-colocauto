<template>
  <div class="register-step box">
    <div v-if="item">
      <div class="note">
        <svg-discussion width="100px" class="p-2"/>
        <p>Sur Coloc'Auto, des groupes de personnes se partagent l'usage d'un ou
        plusieurs véhicules. Pour cela, il se regroupent au sein de communautés.</p>
      </div>

      <h2>Créer votre première communauté</h2>
      <community-form
        :loading="loading"
        :community="item"
        :form="form"
        @reset="reset"
        :changed="changed"
        @submit="submit"
      >
        <b-btn variant="outline-primary" to="/app" class="mr-4" v-on:click="forcePageRefresh()">
          Passer et aller au tableau de bord
        </b-btn>
      </community-form>

    </div>
    <layout-loading v-else />
  </div>
</template>
<script>
import Authenticated from "@/mixins/Authenticated";
import Notification from "@/mixins/Notification";
import UserMixin from "@/mixins/UserMixin";
import FormLabelsMixin from "@/mixins/FormLabelsMixin";
import FormMixin from "@/mixins/FormMixin";

import CommunityForm from "@/components/Community/CommunityForm.vue";
import SvgDiscussion from "@/assets/svg/discussion.svg";

export default {
  name: "RegisterStep",
  mixins: [Authenticated, FormLabelsMixin, FormMixin, Notification, UserMixin],
  components: {
    CommunityForm,
    SvgDiscussion,
  },
  props: {
    id: {
      required: false,
      default: "new",
    },
  },
  methods: {
    forcePageRefresh() {
      // Hack to get the dashboard to refresh with the latest UserMixin
      window.location.reload();
    },
    async afterSubmit() {
      await this.$store.dispatch('invitations/loadEmpty');
      this.$store.state.invitations.item.community_id = this.item.id;
      this.$store.state.invitations.item.community = this.item;
      await this.$store.dispatch('invitations/retrieve', {community_id: this.item.id});
    },
  },
}
</script>
