<template>
  <div class="loan__actions__buttons text-right mb-3"
    v-if="!!item.id && item.loan_status === 'in_process' && !item.canceled_at">
    <b-button class="ml-3 mb-3" variant="danger"
      :disabled="hasReachedStep('takeover') || item.canceled_at"
      @click="$emit('cancel')">
      Annuler la réservation
    </b-button>
    <b-button class="ml-3 mb-3" variant="danger" :disabled="!item.canceled_at"
      @click="$emit('resume')" v-if="(isAdmin || isGlobalAdmin)">
      Réactiver la réservation
    </b-button>
    <b-button v-if="!userIsOwner" class="ml-3 mb-3" variant="warning"
      :disabled="!hasReachedStep('takeover') || hasReachedStep('payment')"
      @click="$emit('extension')">
      Signaler un retard
    </b-button>
    <b-button v-if="!userIsOwner" class="ml-3 mb-3" variant="warning"
      :disabled="!hasReachedStep('takeover')"
      @click="$emit('incident')">
      Signaler un incident
    </b-button>
  </div>
</template>

<script>
import LoanStepsSequence from '@/mixins/LoanStepsSequence';
import UserMixin from '@/mixins/UserMixin';

export default {
  name: 'ActionButtons',
  mixins: [LoanStepsSequence, UserMixin],
  props: {
    item: {
      type: Object,
      required: true,
    },
  },
};
</script>

<style lang="scss">
</style>
