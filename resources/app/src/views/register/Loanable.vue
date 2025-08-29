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
         <b-btn variant="outline-primary" to="/app" class="mt-4" @click="forcePageRefresh">
          Remplir plus tard
        </b-btn>
        <b-btn variant="primary" class="mt-4 ml-4" @click="showExtendedForm">
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
          <b-btn variant="outline-primary" to="/app" class="mr-4" @click="forcePageRefresh">
            Passer et aller au tableau de bord
          </b-btn>
        </loanable-form>

    </template>
    <layout-loading v-else />
  </div>
</template>
<script>
import Vue from "vue";
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
      created: false,
    }
  },
  methods: {
    forcePageRefresh() {
      // Hack to get the dashboard to refresh with the latest UserMixin
      window.location.reload();
    },
    formMixinCallback(){
      // set uset main community as the default loanable community
      this.item.community = this.user.main_community;
      this.item.community_id = this.user.main_community.id;
    },
    afterSubmit() {
      if(this.created) {
        // go to the dashboard
        this.$router.push("/app")
        this.forcePageRefresh()
      } else {
        this.created = true
        this.showConfirmation = true
      }
    },
    showExtendedForm() {
      this.showConfirmation = false
      Vue.nextTick(() => {
        const reportsSection = this.$el.querySelector('.loanable-form #reports')
        if( reportsSection ) reportsSection.scrollIntoView(true)
      })
    },
  },
}
</script>
<style scoped lang="scss">
::v-deep .form__section .form__section {
  margin: 0 -30px;
  .form {
    margin: 0 -30px;
  }
}
</style>
