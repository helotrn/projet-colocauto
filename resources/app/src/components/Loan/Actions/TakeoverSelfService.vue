<template>
  <b-card no-body class="loan-form loan-actions loan-actions-takeover-self-service">
    <b-card-header
      header-tag="header"
      role="tab"
      class="loan-actions__header"
      v-b-toggle.loan-actions-takeover-self-service
    >
      <b-row>
        <b-col>
          <h2>
            <svg-waiting v-if="action.status === 'in_process' && !loanIsCanceled" />
            <svg-check v-else-if="action.status === 'completed'" />
            <svg-danger v-else-if="action.status === 'canceled' || loanIsCanceled" />

            Informations avant de partir
          </h2>
        </b-col>
      </b-row>
      <b-row>
        <b-col>
          <!-- Canceled loans: current step remains in-process. -->
          <span v-if="action.status === 'in_process' && loanIsCanceled">
            Emprunt annulé &bull; {{ item.canceled_at | datetime }}
          </span>
          <span v-else-if="action.status === 'in_process' && !loanIsCanceled"> En attente </span>
          <span v-else-if="action.status === 'completed'">
            Complété &bull; {{ action.executed_at | datetime }}
          </span>
          <span v-else-if="action.status === 'canceled'">
            Annulé &bull; {{ action.executed_at | datetime }}
          </span>
        </b-col>
      </b-row>
    </b-card-header>

    <b-card-body>
      <b-collapse
        id="loan-actions-takeover-self-service"
        role="tabpanel"
        accordion="loan-actions"
        :visible="open"
      >
        <div v-if="action.status === 'in_process' && loanIsCanceled">
          <b-row>
            <b-col>
              <b-alert show variant="danger">
                <p>L'emprunt a été annulé. Cette étape ne peut pas être complétée.</p>
              </b-alert>
            </b-col>
          </b-row>
        </div>
        <div v-if="!loanIsCanceled">
          <b-row>
            <b-col>
              <b-alert show variant="warning" v-if="item.loanable.instructions">
                <div class="alert-heading">
                  <h4>Instructions du propriétaire pour l'utilisation du véhicule</h4>
                </div>
                <div class="owner-instructions-text">
                  <p>{{ item.loanable.instructions }}</p>
                </div>
              </b-alert>
              <b-alert show variant="info">
                <div class="alert-heading"><h4>À savoir</h4></div>
                <div>
                  <p>
                    Assurez-vous d'avoir lu
                    <a href="https://mailchi.mp/solon-collectif/locomotion-comment-ca-marche">
                      le guide de départ
                    </a>
                    avant votre emprunt.
                  </p>
                  <p v-if="item.loanable.has_padlock">
                    Il faut attendre au minimum 5 minutes après avoir fait la réservation pour
                    pouvoir débarrer le cadenas intelligent sur le véhicule!
                  </p>
                  <p v-if="item.loanable.has_padlock">
                    Pour garder l'accès au cadenas intelligent, prolongez votre emprunt avec le
                    bouton «&nbsp;Signaler un retard&nbsp;».
                  </p>
                </div>
              </b-alert>
            </b-col>
          </b-row>

          <b-row>
            <b-col>
              <p class="text-center"><strong>Bonne route!</strong></p>
            </b-col>
          </b-row>

          <b-row
            class="loan-actions-takeover-self-service__buttons text-center"
            v-if="!action.executed_at"
          >
            <b-col>
              <b-button
                @click="completeAction"
                :disabled="actionLoading"
                size="sm"
                variant="success"
              >
                Démarrer l'emprunt
              </b-button>
            </b-col>
          </b-row>
        </div>
      </b-collapse>
    </b-card-body>
  </b-card>
</template>

<script>
import LoanActionsMixin from "@/mixins/LoanActionsMixin";

export default {
  name: "LoanActionsTakeoverSelfService",
  mixins: [LoanActionsMixin],
};
</script>

<style lang="scss">
.owner-instructions-text {
  white-space: pre;
}
</style>
