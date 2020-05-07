<template>
  <b-card no-body class="loan-form loan-actions loan-actions-pre_payment">
    <b-card-header header-tag="header" role="tab" class="loan-actions__header">
      <h2 v-b-toggle.loan-actions-pre_payment>
        <svg-waiting v-if="action.status === 'in_process'" />
        <svg-check v-else-if="action.status === 'completed'" />
        <svg-danger v-else-if="action.status === 'canceled'" />

        Prépaiement
      </h2>

      <span v-if="action.status == 'in_process'">En attente</span>
      <span v-else-if="action.status === 'completed'">
        Complété &bull; {{ action.executed_at | datetime }}
      </span>
      <span v-else-if="action.status === 'canceled'">
        Annulé &bull; {{ action.executed_at | datetime }}
      </span>
    </b-card-header>


    <b-card-body>
      <b-collapse id="loan-actions-pre_payment" role="tabpanel" accordion="loan-actions"
        :visible="open">
        <div class="loan-actions-pre_payment__description text-center mb-3"
          v-if="!action.executed_at">
          <div v-if="userRole === 'owner'">
            <p>
              {{ loan.borrower.user.name }} doit ajouter des crédits à son compte.
            </p>
          </div>
          <div v-else>
            <p v-if="user.balance < (loan.estimated_price + loan.estimated_insurance)">
              Vous devez ajouter des crédits à votre compte.
            </p>
            <p v-else>
              Vous pouvez ajouter des crédits à votre compte.
            </p>

            <user-add-credit-box
              :minimumRequired="minimumRequired"
              :user="user" @bought="completeAction" />

            <div class="loan-actions-intention__buttons text-center"
              v-if="user.balance >= (loan.estimated_price + loan.estimated_insurance)">
              <p>Ou compléter cette étape sans plus attendre.</p>

              <b-button size="sm" variant="success" class="mr-3" @click="completeAction">
                Compléter
              </b-button>

              <b-button size="sm" variant="outline-danger" @click="cancelAction">
                Annuler
              </b-button>
            </div>
          </div>
        </div>
        <div v-else>
          <p>Il y a assez de crédits à votre compte pour couvrir cette course.</p>
          <p>Visitez votre profil pour ajouter des crédits à votre compte.</p>
        </div>
      </b-collapse>
    </b-card-body>
  </b-card>
</template>

<script>
import UserAddCreditBox from '@/components/User/AddCreditBox.vue';

import LoanActionsMixin from '@/mixins/LoanActionsMixin';

export default {
  name: 'LoanActionsPrePayment',
  mixins: [LoanActionsMixin],
  components: {
    UserAddCreditBox,
  },
  computed: {
    minimumRequired() {
      return parseFloat(this.loan.estimated_price)
        + parseFloat(this.loan.estimated_insurance);
    },
  },
};
</script>

<style lang="scss">
</style>
