<template>
  <b-card no-body class="loan-form loan-actions loan-actions-intention">
    <b-card-header header-tag="header" role="tab" class="loan-actions__header">
      <h2 v-b-toggle.loan-actions-intention>
        <svg-waiting v-if="action.status === 'in_process' && !loanIsCanceled" />
        <svg-check v-else-if="action.status === 'completed'" />
        <svg-danger v-else-if="action.status === 'canceled' || loanIsCanceled" />

        Confirmation de l'emprunt
      </h2>

      <!-- Canceled loans: current step remains in-process. -->
      <span v-if="action.status === 'in_process' && loanIsCanceled">
        Emprunt annulé &bull; {{ item.canceled_at | datetime }}
      </span>
      <span v-else-if="action.status === 'in_process'"> En attente d'approbation </span>
      <span v-else-if="action.status === 'completed'">
        Approuvé &bull; {{ action.executed_at | datetime }}
      </span>
      <span v-else-if="action.status === 'canceled'">
        Refusé &bull; {{ action.executed_at | datetime }}
      </span>
    </b-card-header>

    <b-card-body>
      <b-collapse
        id="loan-actions-intention"
        role="tabpanel"
        accordion="loan-actions"
        :visible="open"
      >
        <div v-if="action.status === 'in_process' && loanIsCanceled">
          <p>L'emprunt a été annulé. Cette étape ne peut pas être complétée.</p>
        </div>

        <div
          v-if="action.status !== 'in_process' || !loanIsCanceled"
          class="loan-actions-intention__image mb-3 text-center"
        >
          <user-avatar v-if="userRoles.includes('borrower')" :user="owner.user" />
          <user-avatar v-else-if="userRoles.includes('owner')" :user="borrower.user" />
        </div>

        <div
          v-if="action.status !== 'in_process' || !loanIsCanceled"
          class="loan-actions-intention__description text-center mb-3"
        >
          <div v-if="!loanableIsSelfService && !borrowerIsOwner">
            <p v-if="userRoles.includes('borrower')">
              Vous avez demandé à {{ item.loanable.owner.user.name }} de lui emprunter
              {{ loanablePrettyNameBorrower }}.
            </p>
            <p v-else-if="userRoles.includes('owner')">
              {{ borrower.user.name }} veut vous emprunter {{ loanablePrettyNameOwner }}.
            </p>
          </div>

          <label>Raison de l'emprunt</label>
          <p>{{ item.reason }}</p>

          <blockquote v-if="item.message_for_owner">
            {{ item.message_for_owner }}
            <user-avatar :user="borrower.user" />
          </blockquote>

          <blockquote
            v-if="
              action.message_for_borrower &&
              (userRoles.includes('borrower') || !!action.executed_at)
            "
          >
            {{ action.message_for_borrower }}
            <user-avatar :user="borrower.user" />
          </blockquote>
        </div>

        <div v-if="!action.executed_at && !loanIsCanceled">
          <div class="loan-actions-intention__see-details text-center mb-3">
            <b-button size="sm" variant="outline-info" v-b-toggle.loan-actions-new>
              Voir les détails
            </b-button>
          </div>

          <!-- No use in a borrower leaving a message to himself. -->
          <div v-if="!userRoles.includes('borrower')">
            <div class="loan-actions-intention__message_for_borrower text-center mb-3">
              <forms-validated-input
                type="textarea"
                name="message_for_borrower"
                v-model="action.message_for_borrower"
                label="Laissez un message à l'emprunteur (facultatif)"
              />
            </div>
          </div>

          <div v-if="userRoles.includes('owner')">
            <div class="loan-actions-intention__buttons text-center">
              <b-button
                size="sm"
                variant="success"
                class="mr-3"
                :disabled="actionLoading"
                @click="completeAction"
              >
                Accepter
              </b-button>

              <b-button
                size="sm"
                variant="outline-danger"
                :disabled="actionLoading"
                @click="cancelAction"
              >
                Refuser
              </b-button>
            </div>
          </div>

          <div
            v-if="!loanableIsSelfService && !borrowerIsOwner && userRoles.includes('borrower')"
            class="text-center"
          >
            <p>
              Merci d'avoir enregistré votre demande d'emprunt sur la plateforme! Maintenant,
              contactez votre voisin-e pour voir directement avec lui/elle si son véhicule est
              disponible.
            </p>
            <p>{{ item.loanable.owner.user.phone }}</p>
          </div>

          <div class="loan-actions__alert" v-if="item.loanable.type === 'car'">
            <b-alert variant="warning" show>
              <p>
                Desjardins assurances ne couvrera le trajet que s'il est bien renseigné sur
                LocoMotion! Pensez à accepter et vérifier le pré-paiement de la réservation ici.
              </p>

              <p>
                <router-link to="/assurances-desjardins">
                  Voir les conditions d'assurances
                </router-link>
              </p>
            </b-alert>
          </div>
        </div>
      </b-collapse>
    </b-card-body>
  </b-card>
</template>

<script>
import FormsValidatedInput from "@/components/Forms/ValidatedInput.vue";
import UserAvatar from "@/components/User/Avatar.vue";

import LoanActionsMixin from "@/mixins/LoanActionsMixin";

export default {
  name: "LoanActionsIntention",
  mixins: [LoanActionsMixin],
  components: {
    FormsValidatedInput,
    UserAvatar,
  },
  computed: {
    loanablePrettyNameBorrower() {
      let article;
      let type;

      switch (this.item.loanable.type) {
        case "car":
          article = "sa";
          type = "voiture";
          break;
        case "bike":
          article = "son";
          type = "vélo";
          break;
        case "trailer":
          article = "sa";
          type = "remorque";
          break;
        default:
          article = "son";
          type = "objet";
          break;
      }

      return `${article} ${type}`;
    },
    loanablePrettyNameOwner() {
      const article = "votre";
      let type;

      switch (this.item.loanable.type) {
        case "car":
          type = "voiture";
          break;
        case "bike":
          type = "vélo";
          break;
        case "trailer":
          type = "remorque";
          break;
        default:
          type = "objet";
          break;
      }

      return `${article} ${type}`;
    },
  },
};
</script>

<style lang="scss">
.loan-actions-intention {
  &__image {
    > div {
      margin: 0 auto;
    }
  }
}
</style>
