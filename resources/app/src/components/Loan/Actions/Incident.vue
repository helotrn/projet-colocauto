<template>
  <b-card no-body class="loan-form loan-actions loan-actions-incident"
    :id="`loan-incident-${action.id || 'new'}`">
    <b-card-header header-tag="header" role="tab" class="loan-actions__header">
      <h2 v-b-toggle="`loan-actions-incident-${action.id || 'new'}`">
        <svg-waiting v-if="action.status === 'in_process'" />
        <svg-check v-else-if="action.status === 'completed'" />
        <svg-danger v-else-if="action.status === 'canceled'" />

        Incident
      </h2>

      <span v-if="action.status == 'in_process'">En attente</span>
      <span v-else-if="action.status === 'completed'">
        Validé &bull; {{ action.executed_at | datetime }}
      </span>
      <span v-else-if="action.status === 'canceled'">
        Contesté &bull; {{ action.executed_at | datetime }}
      </span>
    </b-card-header>


    <b-card-body>
      <b-collapse :id="`loan-actions-incident-${action.id || 'new'}`"
        role="tabpanel" accordion="loan-actions"
        :visible="open">
        <div v-if="!action.id">
          <p>
            Cliquez ci-dessous pour indiquer qu'un accident s'est produit et
            entamer les démarches auprès des assurances.
          </p>

          <validation-observer ref="observer" v-slot="{ passes }">
            <b-form :novalidate="true" class="form loan-actions-incident__form"
              @submit.stop.prevent="passes(createAction)"
              @reset.stop.prevent="$emit('reset')">

              <forms-validated-input name="incident_type"
                :rules="{ required: true }"
                label="Type d'incident" type="select"
                :options="[ { value: 'accident', 'text': 'Accident' }]"
                v-model="action.incident_type" />

              <forms-validated-input name="comments_on_incident"
                :rules="{ required: true }"
                label="Commentaire" type="textarea" :rows="3"
                v-model="action.comments_on_incident" />

              <div class="loan-actions-incident__buttons text-center">
                <b-button size="sm" type="submit" variant="success" class="mr-3">
                  Créer
                </b-button>

                <b-button size="sm" variant="outline-warning" @click="abortAction">
                  Annuler
                </b-button>
              </div>
            </b-form>
          </validation-observer>
        </div>
        <div v-else-if="userRole === 'borrower'">
          <p>Un incident s'est produit.</p>

          <p>Procédure d'assurances.</p>
          <p>Contactez les assurances pour entamer le processus.</p>

          <p>Contactez le propriétaire pour qu'il confirme votre demande.</p>
          <p>{{ loan.loanable.owner.user.phone }}</p>
        </div>
        <div v-else>
          <p>
            {{ borrower.user.name }} mentionne qu'un incident s'est produit.
          </p>
        </div>
      </b-collapse>
    </b-card-body>
  </b-card>
</template>

<script>
import FormsValidatedInput from '@/components/Forms/ValidatedInput.vue';

import LoanActionsMixin from '@/mixins/LoanActionsMixin';

export default {
  name: 'LoanActionsIncident',
  mixins: [LoanActionsMixin],
  components: {
    FormsValidatedInput,
  },
};
</script>

<style lang="scss">
</style>
