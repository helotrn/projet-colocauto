<template>
  <b-card class="bordered-card">
    <div class="mb-3 font-weight-bold">{{ coowner.user.full_name }}</div>
    <b-form>
      <forms-validated-input
        v-model="receive_notifications"
        label="Reçoit les notifications"
        name="title"
        type="checkbox"
        :disabled="disabled || loading"
        description="Si cette personne reçoit également les notifications par email lors d'un emprunt"
      ></forms-validated-input>
      <forms-validated-input
        v-model="title"
        label="Titre"
        name="title"
        description="Ce titre clarifie la responsabilité de cette personne pour le véhicule. Ex. dépositaire des clés, copropriétaire, etc."
        type="text"
        :disabled="disabled || loading"
      ></forms-validated-input>

      <div class="mb-3 font-weight-bold">Tarification&nbsp;:</div>

      <forms-validated-input
          v-model="pays_loan_price"
          label="Paie le tarif de l'emprunt"
          name="pays_loan_price"
          description="Si applicable, cette personne paie le coût lié à la durée et à la distance parcourue lors de ses emprunts de ce véhicule."
          type="checkbox"
          :disabled="disabled || loading"
        ></forms-validated-input>
        <forms-validated-input
          v-if="loanable.type == 'car'"
          v-model="pays_provisions"
          label="Paie les provisions mensuelles"
          name="pays_provisions"
          description="Cette personne paie les provisions partagées mensuellement entre les membres du groupe."
          type="checkbox"
          :disabled="disabled || loading"
        ></forms-validated-input>
        <forms-validated-input
          v-if="loanable.type == 'car'"
          v-model="pays_owner"
          label="Paie le dédommagement au propriétaire"
          name="pays_owner"
          description="Cette personne participe au paiement mensuel pour dédommager le ou la propriétaire du véhicule."
          type="checkbox"
          :disabled="disabled || loading"
        ></forms-validated-input>
    </b-form>
    <!-- form with two fields: show on contact and title -->

    <icon-button role="save" :loading="loading" :disabled="disabled" @click.prevent="save">
      Enregistrer
    </icon-button>
    &nbsp;
    <icon-button role="cancel" :disabled="disabled || loading" @click.prevent="cancel">
      Annuler
    </icon-button>
    <layout-loading v-if="loading" with-button />
  </b-card>
</template>
<script>
import FormsValidatedInput from "@/components/Forms/ValidatedInput.vue";
import LayoutLoading from "@/components/Layout/Loading.vue";
import IconButton from "@/components/shared/IconButton.vue";
import Vue from "vue";

export default {
  name: "CoownerForm",
  components: { IconButton, LayoutLoading, FormsValidatedInput },
  props: {
    coowner: {
      type: Object,
      required: true,
    },
    loanable: {
      type: Object,
      required: true,
    },
    disabled: { type: Boolean, default: false },
  },
  data() {
    return {
      receive_notifications: null,
      pays_loan_price: null,
      pays_provisions: null,
      pays_owner: null,
      title: null,
      loading: false,
    };
  },
  mounted() {
    this.receive_notifications = this.coowner.receive_notifications;
    this.pays_loan_price = this.coowner.pays_loan_price;
    this.pays_provisions = this.coowner.pays_provisions;
    this.pays_owner = this.coowner.pays_owner;
    this.title = this.coowner.title;
  },
  methods: {
    cancel() {
      this.$emit("done", this.coowner.id);
    },
    async save() {
      this.loading = true;

      await Vue.axios.put(
        `/loanables/${this.loanable.id}/coowners/${this.coowner.id}`,
        {
          id: this.coowner.id,
          receive_notifications: this.receive_notifications,
          pays_loan_price: this.pays_loan_price,
          pays_provisions: this.pays_provisions,
          pays_owner: this.pays_owner,
          title: this.title,
        }
      ).then(() => {
        this.coowner.receive_notifications = this.receive_notifications;
        this.coowner.pays_loan_price = this.pays_loan_price;
        this.coowner.pays_provisions = this.pays_provisions;
        this.coowner.pays_owner = this.pays_owner;
        this.coowner.title = this.title;
        this.loading = false
        this.$emit("done", this.coowner.id);

        this.$store.commit("addNotification", {
          content: this.coowner.user.full_name,
          title: "Changements sauvegardés !",
          variant: "success",
          type: "loanable",
        })
      }).catch(error => {
        let message = error.request?.response
        if(message) {
          message = JSON.parse(message);
          if( message.errors ){
            message = Object.values(message.errors).join("\n ");
          }
        } else {
          message = error.message;
        }
        this.loading = false

        this.$store.commit("addNotification", {
          content: message,
          title: "Erreur de sauvegarde",
          variant: "danger",
          type: "loanable",
        })
      });

    },
  },
};
</script>
<style scoped lang="scss">
.bordered-card {
  border: 1px solid $light-grey;
}
</style>
