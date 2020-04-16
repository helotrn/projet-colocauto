<template>
  <b-card no-body class="loan-form loan-actions loan-actions-payment">
    <b-card-header header-tag="header" role="tab" class="loan-actions__header">
      <h2 v-b-toggle.loan-actions-payment>
        <svg-waiting v-if="action.status === 'in_process'" />
        <svg-check v-else-if="action.status === 'completed'" />
        <svg-danger v-else-if="action.status === 'canceled'" />

        Conclusion
      </h2>

      <span v-if="action.status == 'in_process'">En attente</span>
      <span v-else-if="action.status === 'completed'">
        Payé &bull; {{ action.executed_at | datetime }}
      </span>
      <span v-else-if="action.status === 'canceled'">
        Annulé &bull; {{ action.executed_at | datetime }}
      </span>
    </b-card-header>


    <b-card-body>
      <b-collapse id="loan-actions-payment" role="tabpanel" accordion="loan-actions"
        :visible="open">
        <div v-if="!!action.executed_at">
          <p>L'emprunt s'est conclu avec succès!</p>

          <p>Le montant final était {{ loan.final_price | currency }}.</p>
        </div>
        <div v-else>
          <div v-if="userRole === 'owner'">
            <p>
              Vous pouvez maintenant valider les données de la course.
            </p>
            <p>
              L'emprunteur sera invité à faire de même avant d'effectuer le paiement final.
            </p>
          </div>
          <div v-if="userRole === 'borrower'" class="text-center">
            <p>
              Vous pouvez maintenant valider les données de la course.
            </p>
            <p>
              Le prix final s'élève à {{ loan.actual_price | currency }}.
            </p>

            <p>
              Le propriétaire sera invité à faire de même.
            </p>

            <p>
              Si tout est en ordre, veuillez valider ci-dessous.
            </p>

            <div class="loan-actions-payment__buttons text-center">
              <b-button size="sm" variant="success" class="mr-3" @click="completeAction">
                Accepter
              </b-button>

              <b-button size="sm" variant="outline-danger" @click="cancelAction">
                Refuser
              </b-button>
            </div>
          </div>
        </div>
      </b-collapse>
    </b-card-body>
  </b-card>
</template>

<script>
import LoanActionsMixin from '@/mixins/LoanActionsMixin';

export default {
  name: 'LoanActionsPrePayment',
  mixins: [LoanActionsMixin],
};
</script>

<style lang="scss">
</style>
