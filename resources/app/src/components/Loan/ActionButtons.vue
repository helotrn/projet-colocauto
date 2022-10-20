<template>
  <div class="loan__actions__buttons flex-column flex-md-row-reverse" v-if="!!item.id">
    <b-button variant="danger" :disabled="loanIsCanceled || !canCancel" @click="$emit('cancel')">
      Annuler la réservation
    </b-button>
    <b-button
      v-if="isAdmin || isGlobalAdmin"
      variant="danger"
      :disabled="!loanIsCanceled"
      @click="$emit('resume')"
    >
      Réactiver la réservation
    </b-button>
    <b-button
      v-if="!userIsOwner"
      variant="warning"
      :disabled="!hasReachedStep('takeover') || hasReachedStep('payment')"
      @click="$emit('extension')"
    >
      Signaler un retard
    </b-button>
    <b-button
      v-if="!userIsOwner"
      variant="warning"
      :disabled="!hasReachedStep('takeover')"
      @click="$emit('incident')"
    >
      Signaler un incident
    </b-button>
  </div>
</template>

<script>
import LoanStepsSequence from "@/mixins/LoanStepsSequence";
import UserMixin from "@/mixins/UserMixin";

export default {
  name: "ActionButtons",
  mixins: [LoanStepsSequence, UserMixin],
  props: {
    // Item is a loan.
    item: {
      type: Object,
      required: true,
    },
  },
  computed: {
    canCancel() {
      // Can cancel if:
      return (
        this.isAdmin ||
        // or the loan is free
        // TODO(#1101) Use a better attribute for this.
        (this.item.estimated_price == 0 && this.item.estimated_insurance == 0) ||
        // or the loanable has not yet been taken
        !this.hasReachedStep("takeover") ||
        // or the reservation has not yet started
        this.$second.isBefore(this.item.departure_at, "minute")
      );
    },
  },
};
</script>

<style lang="scss">
.loan__actions__buttons {
  display: flex;
  gap: 0.5rem;
}
</style>
