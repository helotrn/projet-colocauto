<template>
  <div
    class="loan__actions__buttons text-right mb-3"
    v-if="!!item.id && item.loan_status === 'in_process' && !loanIsCanceled"
  >
    <b-button
      class="mr-0 ml-3 mb-3"
      variant="danger"
      :disabled="hasReachedStep('takeover') || loanIsCanceled"
      @click="$emit('cancel')"
    >
      Annuler la réservation
    </b-button>
    <b-button
      v-if="isAdmin || isGlobalAdmin"
      class="mr-0 ml-3 mb-3"
      variant="danger"
      :disabled="!loanIsCanceled"
      @click="$emit('resume')"
    >
      Réactiver la réservation
    </b-button>
    <b-button
      v-if="!userIsOwner"
      class="mr-0 ml-3 mb-3"
      variant="warning"
      :disabled="!hasReachedStep('takeover') || hasReachedStep('payment')"
      @click="$emit('extension')"
    >
      Signaler un retard
    </b-button>
    <b-button
      v-if="!userIsOwner"
      class="mr-0 ml-3 mb-3"
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
    item: {
      type: Object,
      required: true,
    },
  },
};
</script>

<style lang="scss"></style>
