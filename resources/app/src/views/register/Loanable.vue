<template>
  <div class="register-step box">
    <template v-if="item && showConfirmation">
      <big class="text-center d-block"><strong>Bravo !<br/>
        Votre {{item.name}} à bien été créée
      </strong></big>
      <div class="note mt-4 text-start">
        <svg-pen-paper width="100px" class="p-2"/>
        <p>Pour commencer à le partager, vous devez compléter sa fiche en
        renseignant son état, les droits de gestion, le type de partage des
        coûts et sa disponibilité. </p>
      </div>
      <div class="text-end">
         <b-btn variant="outline-primary" to="/app" class="mt-4" @click="forcePageRefresh()">
          Remplir plus tard
        </b-btn>
        <b-btn variant="primary" to="/app" class="mt-4 ml-4" @click="$router.push('/register/6')">
          Remplir la fiche
      </b-btn>
      </div>
    </template>
    <template v-else-if="item">
      <div class="note">
        <svg-smiling-girl width="100px" class="p-2"/>
        <div>
        <p class="mb-2">
          <big><strong>Bravo !<br/>
          Votre communauté {{user.main_community.name}} à bien été créée et les
          invitations envoyées</strong></big>
        </p>
        <p>Au sein de chaque communauté, vous pouvez partager avec les membres
        l'usage d’un ou plusieurs véhicules.</p>
        </div>
      </div>

      <h2>Ajouter un premier véhicule</h2>
        <loanable-form
          :loanable="item"
          :form="form"
          :loading="loading"
          @submit="submit"
          :changed="changed"
          :center="{}"
        >
          <b-btn variant="outline-primary" to="/app" class="mr-4" v-on:click="forcePageRefresh()">
            Passer et aller au tableau de bord
          </b-btn>
        </loanable-form>

    </template>
    <layout-loading v-else />
  </div>
</template>
<script>
import Authenticated from "@/mixins/Authenticated";
import Notification from "@/mixins/Notification";
import UserMixin from "@/mixins/UserMixin";
import FormMixin from "@/mixins/FormMixin";

import SvgSmilingGirl from "@/assets/svg/smiling-girl.svg";
import SvgPenPaper from "@/assets/svg/pen-paper.svg";
import LoanableForm from "@/components/Loanable/LoanableForm.vue";

export default {
  name: "RegisterLoanable",
  mixins: [Authenticated, FormMixin, Notification, UserMixin],
  components: {
    SvgSmilingGirl,
    SvgPenPaper,
    LoanableForm,
  },
  props: {
    id: {
      required: false,
      default: "new",
    },
  },
  data() {
    return {
      showConfirmation: false,
    }
  },
  methods: {
    forcePageRefresh() {
      // Hack to get the dashboard to refresh with the latest UserMixin
      window.location.reload();
    },
    async afterSubmit() {
      this.showConfirmation = true
    },
    nextStep() {
      this.$router.push("/register/6");
    },
  },
}
</script>
