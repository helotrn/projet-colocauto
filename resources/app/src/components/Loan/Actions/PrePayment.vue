<template>
  <b-card no-body class="loan-form loan-actions loan-actions-pre_payment">
    <b-card-header header-tag="header" role="tab" class="loan-actions__header"
      v-b-toggle.loan-actions-pre_payment>
      <h2>
        <svg-waiting v-if="action.status === 'in_process' && !item.canceled_at" />
        <svg-check v-else-if="action.status === 'completed'" />
        <svg-danger v-else-if="action.status === 'canceled' || item.canceled_at" />

        Prépaiement
      </h2>

      <span v-if="action.status == 'in_process' && !item.canceled_at">En attente</span>
      <span v-else-if="action.status === 'completed'">
        Complété &bull; {{ action.executed_at | datetime }}
      </span>
      <span v-else-if="action.status === 'canceled' || item.canceled_at">
        Annulé &bull; {{ action.executed_at || item.canceled_at | datetime }}
      </span>
    </b-card-header>


    <b-card-body>
      <b-collapse id="loan-actions-pre_payment" role="tabpanel" accordion="loan-actions"
        :visible="open">
        <div class="loan-actions-pre_payment__description text-center mb-3"
          v-if="!action.executed_at">
          <!-- Action is not completed -->
          <div v-if="userRoles.includes('borrower')">
            <p>
              Utiliser votre solde ou payer directement.
            </p>

            <user-add-credit-box
              :minimumRequired="minimumRequired"
              :user="user" @bought="completeAction" />

            <div class="loan-actions-intention__buttons text-center"
              v-if="user.balance >= (item.estimated_price + item.estimated_insurance)">
              <p>Ou compléter cette étape sans plus attendre.</p>

              <b-button size="sm" variant="success" class="mr-3" @click="completeAction">
                Compléter
              </b-button>

              <b-button size="sm" variant="outline-danger" @click="cancelAction">
                Annuler
              </b-button>
            </div>
          </div>
          <div v-else-if="userRoles.includes('owner')">
            <p>
              {{ item.borrower.user.name }} doit ajouter des crédits à son compte.
            </p>
          </div>
        </div>
        <div v-else>
          <!-- Action is completed -->
          <div v-if="userRoles.includes('borrower')">
            <p>Il y a assez de crédits à votre compte pour couvrir cette course.</p>
            <p>Visitez votre profil pour ajouter des crédits à votre compte.</p>
          </div>
          <div v-else-if="userRoles.includes('owner')">
            <p>Il y a assez de crédits au compte de l'emprunteur-se pour couvrir cette course.</p>
            <p>Visitez votre profil pour ajouter des crédits à votre compte.</p>
          </div>
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
      return parseFloat(this.item.estimated_price, 10)
        + parseFloat(this.item.estimated_insurance, 10)
        + parseFloat(this.item.platform_tip, 10);
    },
  },
};
</script>

<style lang="scss">
</style>
