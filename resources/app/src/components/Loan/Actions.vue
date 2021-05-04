<template>
  <div class="loan-actions">
    <loan-form :item="item" :form="form" :open="isCurrentStep('creation')"
      @submit="emitSubmit" :user="user" />

    <div class="loan-actions__action" v-for="action in item.actions" :key="action.id">
      <div v-if="action.type === 'intention'">
        <loan-actions-intention v-if="isOwnedLoanable"
          :action="action" :item="item" :open="isCurrentStep('intention')"
          @completed="emitLoad" @canceled="emitLoad" :user="user" />
      </div>

      <div v-else-if="action.type === 'pre_payment'">
        <loan-actions-pre-payment v-if="item.total_estimated_cost > 0"
          :action="action" :item="item" :open="isCurrentStep('pre_payment')"
          @completed="emitLoad" @canceled="emitLoad" :user="user" />
      </div>

      <div v-else-if="action.type === 'takeover'">
        <loan-actions-takeover v-if="isOwnedLoanable"
          :action="action" :item="item" :open="isCurrentStep('takeover')"
          @completed="emitLoad" @canceled="emitLoad" :user="user" />
        <loan-actions-takeover-collective v-else
          :action="action" :item="item" :open="isCurrentStep('takeover')"
          @completed="emitLoad" @canceled="emitLoad" :user="user" />
      </div>

      <div v-else-if="action.type === 'handover'">
        <loan-actions-handover v-if="isOwnedLoanable"
          :action="action" :item="item" :open="isCurrentStep('handover')"
          @completed="emitLoad" @canceled="emitLoad" :user="user" />
        <loan-actions-handover-collective v-else
          :action="action" :item="item" :open="isCurrentStep('handover')"
          @completed="emitLoad" @canceled="emitLoad" :user="user"
          @extension="$emit('extension')" />
      </div>

      <div v-else-if="action.type === 'payment'">
        <loan-actions-payment v-if="isOwnedLoanable"
          :action="action" :item="item" :open="isCurrentStep('payment')"
          @completed="emitLoad" @canceled="emitLoad" :user="user" />
      </div>

      <loan-actions-extension :open="!action.executed_at"
        v-else-if="action.type === 'extension'"
        :action="action" :item="item" :user="user"
        @aborted="abortAction" @created="emitLoad" @completed="emitLoad"
        @canceled="emitLoad" />

      <loan-actions-incident :open="!action.executed_at"
        v-else-if="action.type === 'incident'"
        :action="action" :item="item" :user="user"
        @aborted="abortAction" @created="emitLoad" @completed="emitLoad"
        @canceled="emitLoad" />

      <p v-else>
        {{ action.type }} n'est pas supporté. Contactez le
        <a href="mailto:support@locomotion.app">support</a>.
      </p>
    </div>
  </div>
</template>

<script>
import LoanForm from '@/components/Loan/Form.vue';
import LoanActionsExtension from '@/components/Loan/Actions/Extension.vue';
import LoanActionsHandover from '@/components/Loan/Actions/Handover.vue';
import LoanActionsHandoverCollective from '@/components/Loan/Actions/HandoverCollective.vue';
import LoanActionsIncident from '@/components/Loan/Actions/Incident.vue';
import LoanActionsIntention from '@/components/Loan/Actions/Intention.vue';
import LoanActionsPayment from '@/components/Loan/Actions/Payment.vue';
import LoanActionsPrePayment from '@/components/Loan/Actions/PrePayment.vue';
import LoanActionsTakeover from '@/components/Loan/Actions/Takeover.vue';
import LoanActionsTakeoverCollective from '@/components/Loan/Actions/TakeoverCollective.vue';

import LoanStepsSequence from '@/mixins/LoanStepsSequence';

export default {
  name: 'Actions',
  mixins: [LoanStepsSequence],
  components: {
    LoanForm,
    LoanActionsExtension,
    LoanActionsHandover,
    LoanActionsHandoverCollective,
    LoanActionsIncident,
    LoanActionsIntention,
    LoanActionsPayment,
    LoanActionsPrePayment,
    LoanActionsTakeover,
    LoanActionsTakeoverCollective,
  },
  props: {
    form: {
      type: Object,
      required: true,
    },
    item: {
      type: Object,
      required: true,
    },
    user: {
      type: Object,
      required: true,
    },
  },
  methods: {
    abortAction(action) {
      const indexOfAction = this.item.actions.indexOf(action);
      this.item.actions.splice(indexOfAction, 1);
    },
    emitLoad() {
      this.$emit('load');
    },
    emitSubmit() {
      this.$emit('submit');
    },
  },
};
</script>

<style lang="scss">
.loan-actions {
  .card {
    margin-bottom: 20px;
  }
}
</style>
