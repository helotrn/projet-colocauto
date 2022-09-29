<template>
  <div class="status-container">
    <div
      v-b-tooltip.hover
      :title="loanStatus.description"
      class="loan-status-pill"
      :class="loanStatus.variant"
    >
      {{ loanStatus.text }}
    </div>
    <div v-if="item.loanable.is_self_service">
      <span class="loan-status-pill info">En libre service</span>
    </div>
  </div>
</template>

<script>
import LoanStepsSequence from "@/mixins/LoanStepsSequence";
import UserMixin from "@/mixins/UserMixin";

export default {
  name: "LoanStatus",
  mixins: [LoanStepsSequence, UserMixin],
  props: {
    // Item is a loan.
    item: {
      type: Object,
      required: true,
    },
  },
  computed: {
    loanStatus() {
      if (!this.item.id || !this.item.intention) {
        return {
          text: "Nouveau emprunt",
          variant: "success",
          description: "Emprunt en cours de création!",
        };
      }
      if (this.loanIsCanceled) {
        return {
          text: "Emprunt annulé",
          variant: "danger",
          description: "L'emprunt n'a pas pu être complété.",
        };
      }
      if (this.hasActiveIncidents) {
        return {
          text: "Incident",
          variant: "danger",
          description: "Un incident est en cours de résolution.",
        };
      }
      if (this.loanIsContested) {
        return {
          text: "Informations contestées",
          variant: "danger",
          description: "Les informations au retour ou au départ ont été contestées.",
        };
      }
      if (this.loanIsCompleted) {
        return {
          text: "Emprunt complété",
          variant: "success",
          description: "L'emprunt a été complété avec succès!",
        };
      }
      if (this.item.intention.status === "in_process") {
        return {
          text: "Attente d'approbation",
          variant: "warning",
          description: "Le propriétaire du véhicule doit approuver la demande d'emprunt.",
        };
      }
      if (this.item.pre_payment.status === "in_process") {
        return {
          text: "Attente de prépaiement",
          variant: "warning",
          description:
            "L'emprunteur-se doir ajouter des fonds à son solde avant de débuter l'emprunt.",
        };
      }
      if (this.item.takeover.status === "in_process") {
        if (this.item.loanable.is_self_service) {
          if (this.$dayjs(this.item.actual_return_at).isBefore(this.$dayjs(), "minute")) {
            return {
              text: "Réservation expirée",
              variant: "warning",
              description:
                "La réservation du véhicule est terminée. Vous pouvez clore l'emprunt en ligne.",
            };
          }
          if (this.$dayjs(this.item.departure_at).isBefore(this.$dayjs(), "minute")) {
            return {
              text: "Réservation en cours",
              variant: "warning",
              description: "La réservation du véhicule est en cours! Démarrez l'emprunt en ligne.",
            };
          }
          return {
            text: "Réservation confirmée",
            variant: "success",
            description: "La réservation est confirmée!",
          };
        }

        if (this.$dayjs(this.item.actual_return_at).isBefore(this.$dayjs(), "minute")) {
          return {
            text: "Réservation expirée",
            variant: "warning",
            description:
              "La réservation du véhicule est terminée. Vous pouvez annuler l'emprunt en ligne.",
          };
        }
        if (this.$dayjs(this.item.departure_at).isBefore(this.$dayjs(), "minute")) {
          return {
            text: "Attente du début de l'emprunt",
            variant: "warning",
            description: "La réservation du véhicule est en cours! Démarrez l'emprunt en ligne.",
          };
        }
        return {
          text: "Emprunt confirmé",
          variant: "success",
          description:
            "L'emprunt est confirmé! Remplissez l'information en ligne lorsque l'emprunteur prend possession du véhicule.",
        };
      }
      if (
        this.item.extensions &&
        this.item.extensions.reduce((acc, e) => acc || e.status === "in_process", false)
      ) {
        return {
          text: "Attente d'approbation de retard",
          variant: "warning",
          description: "Le propriétaire doit approuver la demande de retard du véhicule.",
        };
      }
      if (this.item.handover.status === "in_process") {
        if (this.item.loanable.is_self_service) {
          if (this.$dayjs(this.item.actual_return_at).isBefore(this.$dayjs(), "minute")) {
            return {
              text: "Réservation terminée",
              variant: "warning",
              description:
                "La réservation du véhicule est terminée. Veuillez le retourner et compléter les étapes l'emprunt en ligne.",
            };
          }
          if (this.$dayjs(this.item.departure_at).isAfter(this.$dayjs(), "minute")) {
            return {
              text: "Réservation confirmée",
              variant: "success",
              description: "La réservation est confirmée.",
            };
          }
          return {
            text: "Réservation en cours",
            variant: "success",
            description: "La réservation du véhicule est en cours!",
          };
        }

        if (this.$dayjs(this.item.actual_return_at).isBefore(this.$dayjs(), "minute")) {
          return {
            text: "Attente de fin d'emprunt",
            variant: "warning",
            description:
              "La réservation du véhicule est terminée. Veuillez le retourner et compléter les étapes l'emprunt en ligne.",
          };
        }

        if (this.$dayjs(this.item.departure_at).isAfter(this.$dayjs(), "minute")) {
          return {
            text: "Emprunt confirmé",
            variant: "success",
            description:
              "L'emprunt est confirmé. Contactez le propriétaire pour prendre possession du véhicule.",
          };
        }
        return {
          text: "Emprunt en cours",
          variant: "success",
          description: "L'emprunt du véhicule est en cours!",
        };
      }
      if (this.item.payment.status === "in_process") {
        if (this.item.loanable.is_self_service) {
          return {
            text: "Attente de la fin de l'emprunt",
            variant: "warning",
            description:
              "Merci pour votre emprunt! Vous pouvez maintenant clore l'emprunt en ligne et offrir une contribution volontaire.",
          };
        }
        return {
          text: "Attente de paiement",
          variant: "warning",
          description: "L'emprunteur doit payer l'emprunt en ligne.",
        };
      }
      return {};
    },
  },
};
</script>

<style lang="scss">
.status-container {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}
.loan-status-pill {
  display: inline-block;
  font-size: 0.8em;
  padding: 0.2em 0.5em;
  border-radius: 0.25em;
  font-weight: 600;
  &.warning {
    color: $content-alert-warning;
    background-color: $background-alert-warning;
  }
  &.success {
    color: $content-alert-positive;
    background-color: $background-alert-positive;
  }
  &.danger {
    color: $content-alert-negative;
    background-color: $background-alert-negative;
  }
  &.info {
    background-color: $background-alert-informative;
    color: $content-alert-informative;
  }
}
</style>
