<template>
  <b-card
    no-body
    class="loan-form loan-actions loan-actions-extension"
    :id="`loan-extension-${action.id || 'new'}`"
  >
    <b-card-header
      header-tag="header"
      role="tab"
      class="loan-actions__header"
      v-b-toggle="`loan-actions-extension-${action.id || 'new'}`"
    >
      <b-row>
        <b-col>
          <h2>
            <svg-waiting v-if="action.status === 'in_process' && !loanIsCanceled" />
            <svg-check v-else-if="action.status === 'completed'" />
            <svg-danger v-else-if="action.status === 'canceled' || loanIsCanceled" />

            Retard
          </h2>

          <!-- Canceled loans: current step remains in-process. -->
          <span v-if="action.status === 'in_process' && loanIsCanceled">
            Emprunt annulé &bull; {{ item.canceled_at | datetime }}
          </span>
          <span v-else-if="(action.status == 'in_process') & !loanIsCanceled"> En attente </span>
          <span v-else-if="action.status === 'completed'">
            Validé &bull; {{ action.executed_at | datetime }}
          </span>
          <span v-else-if="action.status === 'canceled'">
            Contesté &bull; {{ action.executed_at | datetime }}
          </span>
        </b-col>

        <b-col lg="8" v-if="item.status !== 'completed' && !loanIsCanceled">
          <loan-next-date :loanable-id="item.loanable.id" :loan-id="item.id" />
        </b-col>
      </b-row>
    </b-card-header>

    <b-card-body>
      <b-collapse
        :id="`loan-actions-extension-${action.id || 'new'}`"
        role="tabpanel"
        accordion="loan-actions"
        :visible="open"
      >
        <div v-if="!!action.executed_at">
          <!-- Action is completed -->
          <p>Demande d'extension jusqu'au {{ returnAt | datetime }}.</p>

          <blockquote v-if="action.comments_on_extension">
            {{ action.comments_on_extension }}
            <div class="user-avatar" :style="{ backgroundImage: borrowerAvatar }" />
          </blockquote>

          <blockquote v-if="action.message_for_borrower">
            {{ action.message_for_borrower }}
            <div class="user-avatar" :style="{ backgroundImage: ownerAvatar }" />
          </blockquote>
        </div>
        <div v-else-if="action.status === 'in_process' && loanIsCanceled">
          <p>L'emprunt a été annulé. Cette étape ne peut pas être complétée.</p>
        </div>
        <div v-else-if="userRoles.includes('borrower')">
          <div v-if="!action.id">
            <p>Indiquez une nouvelle heure de retour et laissez un message.</p>

            <validation-observer ref="observer" v-slot="{ passes }">
              <b-form
                :novalidate="true"
                class="form loan-actions-extension__form"
                @submit.stop.prevent="passes(createAction)"
                @reset.stop.prevent="$emit('reset')"
              >
                <b-row>
                  <b-col lg="6">
                    <forms-validated-input
                      name="return_at"
                      :rules="{ required: true }"
                      label="Nouvelle date de retour"
                      type="datetime"
                      :disabled-dates="disabledDates"
                      :disabled-times="disabledTimes"
                      v-model="returnAt"
                    />
                  </b-col>
                  <b-col>
                    <forms-validated-input
                      name="comments_on_extension"
                      :rules="{ required: true }"
                      label="Commentaire"
                      type="textarea"
                      :rows="3"
                      v-model="action.comments_on_extension"
                    />
                  </b-col>
                </b-row>

                <div class="loan-actions-extension__buttons text-center">
                  <b-button size="sm" type="submit" variant="success" class="mr-3" :disabled="!extensionValid">
                    Créer
                  </b-button>

                  <b-button size="sm" variant="outline-warning" @click="abortAction">
                    Annuler
                  </b-button>
                </div>

                <div v-if="!extensionValid" class="loan-actions__alert">
                  <b-alert variant="danger" show>
                    <p>
                      La nouvelle date de retour n'est pas valide. La durée minimale d'un retard doit être d'au moins 10 minutes. 
                    </p>
                    <p class="loan-extension__bold">
                      Date de retour initiale: {{ initialReturnDate | date }} à {{ initialReturnDate | time }}
                    </p>
                  </b-alert>
                </div>
              </b-form>
            </validation-observer>
          </div>

          <div v-else-if="!action.executed_at" class="text-center">
            <p>Demande d'extension jusqu'au {{ returnAt | datetime }}.</p>
            <p>
              Merci d'avoir enregistré votre demande d'extension sur la plateforme! Maintenant,
              contactez votre voisin-e pour voir directement avec lui/elle si son véhicule est
              disponible.
            </p>
            <p v-if="item.loanable.owner">{{ item.loanable.owner.user.phone | phone }}</p>
          </div>
        </div>

        <div v-else>
          <p>{{ borrower.user.name }} demande une extension jusqu'au {{ returnAt | datetime }}.</p>

          <blockquote v-if="action.comments_on_extension">
            {{ action.comments_on_extension }}
            <div class="user-avatar" :style="{ backgroundImage: borrowerAvatar }" />
          </blockquote>

          <div class="loan-actions-extension__message_for_borrower text-center mb-3">
            <forms-validated-input
              type="textarea"
              name="message_for_borrower"
              v-model="action.message_for_borrower"
              label="Laissez un message à l'emprunteur (facultatif)"
            />
          </div>

          <div class="loan-actions-extension__buttons text-center">
            <b-button size="sm" variant="success" class="mr-3" @click="completeAction">
              Accepter
            </b-button>

            <b-button size="sm" variant="outline-danger" @click="cancelAction"> Refuser </b-button>
          </div>

          <div class="loan-actions__alert">
            <b-alert variant="warning" show>
              Les informations de l'emprunt peuvent être modifiées jusqu'à 48h après sa conclusion.
              À partir de ce moment, le coût de l'emprunt sera validé avec les détails ci-dessus.
            </b-alert>
          </div>
        </div>
      </b-collapse>
    </b-card-body>
  </b-card>
</template>

<script>
import FormsValidatedInput from "@/components/Forms/ValidatedInput.vue";

import LoanNextDate from "@/components/Loan/NextDate.vue";

import LoanFormMixin from "@/mixins/LoanFormMixin";
import LoanActionsMixin from "@/mixins/LoanActionsMixin";

const {
  computed: { disabledDates, disabledTimes },
} = LoanFormMixin;

export default {
  name: "LoanActionsExtension",
  mixins: [LoanActionsMixin],
  components: {
    FormsValidatedInput,
    LoanNextDate,
  },
  computed: {
    disabledDates,
    disabledTimes,
    returnAt: {
      get() {
        return this.$dayjs(this.item.departure_at)
          .add(this.action.new_duration, "minute")
          .format("YYYY-MM-DD HH:mm:ss");
      },
      set(val) {
        this.action.new_duration = this.$dayjs(val).diff(
          this.$dayjs(this.item.departure_at),
          "minute"
        );
      },
    },
    initialReturnDate() {
      return this.$dayjs(this.item.departure_at)
      .add(this.item.actual_duration_in_minutes, "minute")
      .format("YYYY-MM-DD HH:mm:ss");
    },
    extensionValid() {
      return (this.action.new_duration > this.item.actual_duration_in_minutes) ? true : false;
    },
  },
};
</script>

<style lang="scss">
.loan-extension {
  &__bold {
    font-weight: 600;
  }
}
</style>
