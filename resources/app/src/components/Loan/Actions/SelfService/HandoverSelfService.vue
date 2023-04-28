<template>
  <b-card
    no-body
    class="loan-form loan-actions loan-actions-handover loan-actions-handover-self-service"
  >
    <b-card-header
      header-tag="header"
      role="tab"
      class="loan-actions__header"
      v-b-toggle.loan-actions-handover-self-service
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
          <span v-if="loanIsCanceled">
            Emprunt annulé &bull; {{ item.canceled_at | datetime }}
          </span>
          <span v-else-if="action.status == 'in_process'"> En attente </span>
          <span v-else-if="action.status === 'completed'">
            Complété &bull; {{ action.executed_at | datetime }}
          </span>
        </b-col>
      </b-row>
    </b-card-header>

    <b-card-body>
      <b-collapse
        id="loan-actions-handover-self-service"
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
        <div v-if="!loanIsCanceled" class="loan-actions-handover-self-service__text">
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
                    <a href="https://www.colocauto.org/faq">
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

          <div
            v-if="!action.executed_at && !loanIsCanceled"
            class="loan-actions-handover-self-service text-center"
          >
            <b-button
              size="sm"
              variant="success"
              :disabled="actionLoading || startsInTheFuture"
              @click="completeHandover"
            >
              Terminer l'emprunt
            </b-button>
            <br />
            <div v-if="startsInTheFuture">
              <small class="text-muted">
                Il sera possible de terminer l'emprunt après l'heure du début de la réservation ({{
                  item.departure_at | shortDate
                }}
                à {{ item.departure_at | time }}).
              </small>
            </div>
          </div>
        </div>
      </b-collapse>
    </b-card-body>
  </b-card>
</template>

<script>
import FormsImageUploader from "@/components/Forms/ImageUploader.vue";
import FormsValidatedInput from "@/components/Forms/ValidatedInput.vue";

import UserAddCreditBox from "@/components/User/AddCreditBox.vue";
import UserAvatar from "@/components/User/Avatar.vue";
import { shortDate } from "@/helpers/filters";

import LoanActionsMixin from "@/mixins/LoanActionsMixin";

export default {
  name: "LoanActionsHandoverSelfService",
  mixins: [LoanActionsMixin],
  components: {
    FormsImageUploader,
    FormsValidatedInput,
    UserAddCreditBox,
    UserAvatar,
  },
  computed: {
    startsInTheFuture() {
      return this.$second.isBefore(this.item.departure_at, "minute");
    },
  },
  methods: {
    completeHandover() {
      if (!this.item.actual_price) {
        this.item.actual_price = 0;
      }

      if (!this.item.actual_insurance) {
        this.item.actual_insurance = 0;
      }
      this.completeAction();
    },
  },
};
</script>

<style lang="scss"></style>
