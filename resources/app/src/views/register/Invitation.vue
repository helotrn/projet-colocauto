<template>
  <div class="register-step box">
    <div class="note">
      <svg-discussion width="100px" class="p-2"/>
      <p>Sur Coloc'Auto, des groupes de personnes se partagent l'usage d'un ou
      plusieurs véhicules. Pour cela, ils se regroupent au sein de communautés.</p>
    </div>
    <h2>{{$route.query.community}}</h2>
    <b-container>
      <b-row>
        <b-col><b-badge variant="warning" class="mb-4 w-full">Vous avez reçu une invitation pour cette communauté</b-badge></b-col>
      </b-row>
      <b-row class="form__buttons">
        <b-col cols="5"><b-btn variant="primary" class="mr-4 w-full" @click="accept">Accepter</b-btn></b-col>
        <b-col><b-btn variant="outline-primary" class="w-full" to="/app">Refuser et aller au tableau de bord</b-btn></b-col>
      </b-row>
    </b-container>
  </div>
</template>
<script>
import Authenticated from "@/mixins/Authenticated";
import Notification from "@/mixins/Notification";

import SvgDiscussion from "@/assets/svg/discussion.svg";

export default {
  name: "RegisterInvitation",
  mixins: [Authenticated, Notification],
  components: {
    SvgDiscussion,
  },
  methods: {
    async accept() {
      try {
        await this.$store.dispatch("invitations/accept", this.$route.query.invitation);
        this.$store.commit("addNotification", {
          content: `Vous avez rejoint la communauté ${this.$route.query.community}.`,
          title: "Invitation acceptée !",
          variant: "success",
          type: "register",
        })

        await this.$store.dispatch("loadUser");
        this.$router.push("/app");
      } catch (e) {
        if (e.request) {
          switch (e.request.status) {
            case 400:
            case 403:
            case 422:
            default:
              this.$store.commit("addNotification", {
                content: e.response.data.message,
                title: "Erreur",
                variant: "danger",
                type: "register",
              });
          }
        }
      }
    },
  },
}
</script>
