<template>
  <b-card class="bordered-card">
    <div class="mb-3 font-weight-bold">{{ coowner.user.full_name }}</div>
    <b-form>
      <forms-validated-input
        v-model="show_as_contact"
        label="Afficher comme contact"
        name="title"
        type="checkbox"
        :disabled="disabled || loading"
        description="Si ses coordonnées devraient être affichées aux emprunteurs-ses."
      ></forms-validated-input>
      <forms-validated-input
        v-model="title"
        label="Titre"
        name="title"
        description="Ce titre clarifie la responsabilité de cette personne pour le véhicule. Ex. marraine/parrain, gardien-ne, coordinateur-rice, copropriétaire, etc."
        type="text"
        :disabled="disabled || loading"
      ></forms-validated-input>

      <template v-if="canEditPaidAmounts">
        <div class="mb-3 font-weight-bold">Tarification&nbsp;:</div>

        <forms-validated-input
          v-model="pays_loan_price"
          label="Paie le tarif de l'emprunt"
          name="pays_loan_price"
          description="Si applicable, cette personne paie le coût lié à la durée et à la distance parcourue lors de ses emprunts de ce véhicule."
          type="checkbox"
          :disabled="disabled || loading"
        ></forms-validated-input>
        <template v-if="hasPaidInsurance || !pays_loan_insurance">
          <forms-validated-input
            v-model="pays_loan_insurance"
            label="Paie l'assurance liée à l'emprunt"
            name="pays_loan_insurance"
            description="Si applicable, cette personne paie le coût lié à l'assurance lors de ses emprunts de ce véhicule."
            type="checkbox"
            :disabled="disabled || loading"
          ></forms-validated-input>
          <b-alert v-if="!pays_loan_insurance" variant="warning" show>
            <strong>Attention&nbsp;:</strong> cette personne ne sera pas assurée par LocoMotion lors
            de ses trajets.
          </b-alert>
        </template>
      </template>
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

export default {
  name: "CoownerForm",
  components: { IconButton, LayoutLoading, FormsValidatedInput },
  props: {
    coowner: {
      type: Object,
      required: true,
    },
    canEditPaidAmounts: {
      type: Boolean,
      default: false,
    },
    disabled: { type: Boolean, default: false },
    hasPaidInsurance: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      show_as_contact: null,
      title: null,
      pays_loan_price: null,
      pays_loan_insurance: null,
      loading: false,
    };
  },
  mounted() {
    this.show_as_contact = this.coowner.show_as_contact;
    this.title = this.coowner.title;
    this.pays_loan_price = this.coowner.pays_loan_price;
    this.pays_loan_insurance = this.coowner.pays_loan_insurance;
  },
  methods: {
    cancel() {
      this.$emit("done", this.coowner.id);
    },
    async save() {
      this.loading = true;

      let paidAmountParams = {};
      if (this.canEditPaidAmounts) {
        paidAmountParams = {
          pays_loan_price: this.pays_loan_price,
          pays_loan_insurance: this.pays_loan_insurance,
        };
      }

      await Vue.ajax.put(
        "/loanables/roles/" + this.coowner.id,
        {
          id: this.coowner.id,
          show_as_contact: this.show_as_contact,
          title: this.title,
          ...paidAmountParams,
        },
        {
          cleanupCallback: () => (this.loading = false),
          notifications: {
            action: "changement",
            onSuccess: "changements sauvegardés!",
          },
        }
      );

      this.coowner.show_as_contact = this.show_as_contact;
      this.coowner.title = this.title;
      this.coowner.pays_loan_price = this.pays_loan_price;
      this.coowner.pays_loan_insurance = this.pays_loan_insurance;
      this.$emit("done", this.coowner.id);
    },
  },
};
</script>
<style scoped lang="scss">
.bordered-card {
  border: 1px solid $light-grey;
}
</style>
