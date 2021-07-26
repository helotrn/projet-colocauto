<template>
  <div class="profile-borrower" v-if="item && routeDataLoaded">
    <div v-if="item.borrower.is_complete" class="profile-borrower__completed-alert">
      <b-alert v-if="!item.borrower.validated" variant="warning" show>
        <p>
          Votre profil d'emprunteur est complet et doit maintenant être validé par l'équipe de
          LocoMotion.
        </p>
      </b-alert>
      <b-alert v-else variant="success" show>
        <p>Votre profil d'emprunteur est complet et a été validé par l'équipe de LocoMotion.</p>
        <p>Contactez le support si vous désirez y apporter des changements.</p>
      </b-alert>
    </div>

    <b-alert variant="info" show>
      Les informations ci-contre ne sont requises que si vous désirez emprunter des voitures.<br />
      Celles-ci ne seront accessibles que par l'équipe de LocoMotion ou les représentants des
      assurances Desjardins.<br />
      Consultez notre <a href="/privacy" target="_blank">politique de confidentialité</a>.
    </b-alert>

    <borrower-form
      :loading="loading"
      :borrower="item.borrower"
      @reset="reset"
      :changed="changed"
      show-reset
      :disabled="item.borrower.is_complete"
      :hide-buttons="item.borrower.is_complete"
      @submit="submit"
      v-if="item"
    />
  </div>
  <layout-loading v-else />
</template>

<script>
import BorrowerForm from "@/components/Borrower/BorrowerForm.vue";

import DataRouteGuards from "@/mixins/DataRouteGuards";
import FormMixin from "@/mixins/FormMixin";

export default {
  name: "ProfileBorrower",
  mixins: [DataRouteGuards, FormMixin],
  components: { BorrowerForm },
  props: {
    id: {
      required: false,
      default: "me",
    },
  },
};
</script>

<style lang="scss">
.profile-borrower {
  &__completed-alert {
    p:last-child {
      margin-bottom: 0;
    }
  }
}
</style>
