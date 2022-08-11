<template>
  <b-card no-body class="loan-form loan-actions loan-actions-pre_payment">
    <b-card-header
      header-tag="header"
      role="tab"
      class="loan-actions__header"
      v-b-toggle.loan-actions-pre_payment
    >
      <h2>
        <svg-waiting v-if="action.status === 'in_process' && !loanIsCanceled" />
        <svg-check v-else-if="action.status === 'completed'" />
        <svg-danger v-else-if="action.status === 'canceled' || loanIsCanceled" />

        Prépaiement
      </h2>

      <!-- Canceled loans: current step remains in-process. -->
      <span v-if="action.status === 'in_process' && loanIsCanceled">
        Emprunt annulé &bull; {{ item.canceled_at | datetime }}
      </span>
      <span v-else-if="action.status == 'in_process'"> En attente </span>
      <span v-else-if="action.status === 'completed'">
        Complété &bull; {{ action.executed_at | datetime }}
      </span>
      <span v-else-if="action.status === 'canceled'">
        Annulé &bull; {{ action.executed_at | datetime }}
      </span>
    </b-card-header>

    <b-card-body>
      <b-collapse
        id="loan-actions-pre_payment"
        role="tabpanel"
        accordion="loan-actions"
        :visible="open"
      >
        <div class="loan-actions-pre_payment__description" v-if="!action.executed_at">
          <!-- Action is not completed -->
          <div v-if="action.status === 'in_process' && loanIsCanceled">
            <p>L'emprunt a été annulé. Cette étape ne peut pas être complétée.</p>
          </div>
          <div v-else-if="userRoles.includes('borrower')">
            <p>
              Ajoutez des fonds a votre solde LocoMotion afin de pouvoir couvrir le coût votre
              emprunt. Ce montant correspond à un estimé. Il sera réevalué en fin d'emprunt pour
              vous donner le montant exact correspondant à vos dépenses (kilometrage réel effectué
              et durée de l'emprunt). C'est à ce moment que les fonds seront retirés de votre solde.
            </p>
            <b-alert v-if="canComplete" show variant="success">
              <p class="font-weight-bold alert-heading">Solde suffisant</p>
              <p>
                Vous avez assez de fonds dans votre solde LocoMotion pour cet emprunt. Vous pouvez
                continuer à l'étape suivante ou ajouter davantage de fonds pour couvrir la possible
                différence entre le coût final et votre estimation.
              </p>
              <b-button variant="success" :disabled="actionLoading" @click="completeAction">
                Continuer à l'étape suivante
              </b-button>
            </b-alert>
            <b-row class="py-2">
              <b-col sm="8" md="6" xl="4" class="font-weight-bold"
                >Coût estimé (trajet + assurance)</b-col
              >
              <b-col col md="2" class="tabular-nums text-sm-right mt-1 mt-sm-0">{{
                this.minimumCost | currency
              }}</b-col>
            </b-row>
            <b-row class="py-2">
              <b-col sm="8" md="6" xl="4" class="font-weight-bold"
                >Contribution volontaire
                <b-form-text>Peut être modifiée lors du paiement final</b-form-text></b-col
              >
              <b-col col md="2" class="tabular-nums text-sm-right mt-1 mt-sm-0">{{
                this.item.platform_tip | currency
              }}</b-col>
            </b-row>
            <b-row class="py-2">
              <b-col sm="8" md="6" xl="4" class="font-weight-bold">Solde actuel</b-col>
              <b-col col md="2" class="tabular-nums text-sm-right mt-1 mt-sm-0">{{
                user.balance | currency
              }}</b-col>
            </b-row>

            <hr />

            <user-add-credit-box
              :minimum-required="this.minimumCost - this.user.balance"
              :trip-cost="this.item.total_estimated_cost"
              :payment-methods="user.payment_methods"
              :no-cancel="true"
              @bought="completeAction"
            />
          </div>
          <div v-else-if="userRoles.includes('owner')">
            <p>{{ item.borrower.user.name }} doit ajouter des crédits à son compte.</p>
          </div>
        </div>
        <div v-else-if="action.status === 'in_process'">
          <!-- Action is in process -->
          <div v-if="userRoles.includes('borrower')">
            <p>Il y a assez de crédits à votre compte pour couvrir cette course.</p>
            <p>Visitez votre profil pour ajouter des crédits à votre compte.</p>
          </div>
          <div v-else-if="userRoles.includes('owner')">
            <p>Il y a assez de crédits au compte de l'emprunteur-se pour couvrir cette course.</p>
            <p>Visitez votre profil pour ajouter des crédits à votre compte.</p>
          </div>
        </div>
        <div v-else-if="action.status === 'canceled'">
          <!-- Action is cancled -->
          <p>Cette étape a été annulée.</p>
        </div>
        <div v-else>
          <!-- Action is completed -->
          <p>Cette étape est complétée.</p>
        </div>
      </b-collapse>
    </b-card-body>
  </b-card>
</template>

<script>
import UserAddCreditBox from "@/components/User/AddCreditBox.vue";

import LoanActionsMixin from "@/mixins/LoanActionsMixin";

export default {
  name: "LoanActionsPrePayment",
  mixins: [LoanActionsMixin],
  components: {
    UserAddCreditBox,
  },
  computed: {
    minimumCost() {
      return parseFloat(this.item.estimated_price) + parseFloat(this.item.estimated_insurance);
    },
    /*
      Can complete if balance is sufficient to cover price and insurance.
      It is not necessary to cover tip as it may be changed later.
    */
    canComplete() {
      return parseFloat(this.user.balance) >= this.minimumCost;
    },
  },
};
</script>

<style lang="scss"></style>
