<template>
  <div class="loan__actions__buttons flex-column flex-md-row-reverse" v-if="!!item.id && actions.includes('cancel')">
    <b-button variant="danger" :disabled="loanIsCanceled || !canCancel || !isBorrower" @click="$emit('cancel')">
      Annuler la réservation
    </b-button>
    <b-button
      v-if="(isAdmin || isGlobalAdmin) && actions.includes('resume')"
      variant="danger"
      :disabled="!loanIsCanceled"
      @click="$emit('resume')"
    >
      Réactiver la réservation
    </b-button>
    <b-button
      v-if="!userIsOwner && actions.includes('extension')"
      variant="warning"
      :disabled="!hasReachedStep('takeover') || hasReachedStep('payment')"
      @click="$emit('extension')"
    >
      Signaler un retard
    </b-button>
    <b-button
      v-if="!userIsOwner && actions.includes('incident')"
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
    actions: {
      type: Array,
      default: () => ['cancel', 'resume']
    }
  },
  computed: {
    canCancel() {
      // Can cancel if:
      return (
        this.isAdmin ||
        this.item.is_free ||
        // or the loanable has not yet been taken
        !this.hasReachedStep("takeover") ||
        // or the reservation has not yet started
        this.$second.isBefore(this.item.departure_at, "minute")
      );
    },
    isBorrower() {
      return this.user.borrower.id === this.item.borrower.id;
    }
  },
};
</script>

<style lang="scss">
.loan__actions__buttons {
  display: flex;
  gap: 0.5rem;
}
</style>
