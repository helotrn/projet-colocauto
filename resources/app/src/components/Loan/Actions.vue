<template>
  <div class="loan-actions">
    <loan-form :loan="item" :form="form" :open="isCurrentStep('creation')"
      @submit="emitSubmit" />

    <div class="loan-actions__action" v-for="action in item.actions" :key="action.id">
      <loan-actions-intention v-if="action.type === 'intention'"
        :action="action" :loan="item" :open="isCurrentStep('intention')"
        @completed="emitLoad" @canceled="emitLoad" :user="user" />
      <loan-actions-pre-payment v-else-if="action.type === 'pre_payment'"
        :action="action" :loan="item" :open="isCurrentStep('pre_payment')"
        @completed="emitLoad" @canceled="emitLoad" :user="user" />
      <loan-actions-takeover v-else-if="action.type === 'takeover'"
        :action="action" :loan="item" :open="isCurrentStep('takeover')"
        @completed="emitLoad" @canceled="emitLoad" :user="user" />
      <loan-actions-handover v-else-if="action.type === 'handover'"
        :action="action" :loan="item" :open="isCurrentStep('handover')"
        @completed="emitLoad" @canceled="emitLoad" :user="user" />
      <loan-actions-payment v-else-if="action.type === 'payment'"
        :action="action" :loan="item" :open="isCurrentStep('payment')"
        @completed="emitLoad" @canceled="emitLoad" :user="user" />
      <loan-actions-extension :open="true"
        v-else-if="action.type === 'extension'"
        :action="action" :loan="item" :user="user"
        @aborted="abortAction" @created="emitLoad" @completed="emitLoad"
        @canceled="emitLoad" />
      <span v-else>
        {{ action.type }} n'est pas supporté. Contactez le
        <a href="mailto:support@locomotion.app">support</a>.
      </span>
    </div>
  </div>
</template>

<script>
import LoanForm from '@/components/Loan/Form.vue';
import LoanActionsExtension from '@/components/Loan/Actions/Extension.vue';
import LoanActionsHandover from '@/components/Loan/Actions/Handover.vue';
import LoanActionsIntention from '@/components/Loan/Actions/Intention.vue';
import LoanActionsPayment from '@/components/Loan/Actions/Payment.vue';
import LoanActionsPrePayment from '@/components/Loan/Actions/PrePayment.vue';
import LoanActionsTakeover from '@/components/Loan/Actions/Takeover.vue';

import LoanStepsSequence from '@/mixins/LoanStepsSequence';

export default {
  name: 'Actions',
  mixins: [LoanStepsSequence],
  components: {
    LoanForm,
    LoanActionsExtension,
    LoanActionsHandover,
    LoanActionsIntention,
    LoanActionsPayment,
    LoanActionsPrePayment,
    LoanActionsTakeover,
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
      const indexOfAction = this.actions.indexOf(action);
      this.actions.splice(indexOfAction, 1);
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
