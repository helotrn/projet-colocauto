<template>
  <div class="loan-actions">
    <loan-form
      :item="item"
      :form="form"
      :open="isCurrentStep('creation')"
      @submit="emitSubmit"
      :user="user"
    />

    <div class="loan-actions__action" v-for="action in item.actions" :key="action.id">
      <div v-if="action.type === 'intention' && displayStep('intention')">
        <!-- hide intention because loan is accepted automatically -->
        <loan-actions-intention
          v-if="false"
          :action="action"
          :item="item"
          :open="isCurrentStep('intention')"
          @completed="emitLoad"
          @canceled="emitLoad"
          :user="user"
        />
      </div>

      <div v-else-if="action.type === 'pre_payment' && displayStep('pre_payment')">
        <loan-actions-pre-payment
          :action="action"
          :item="item"
          :open="isCurrentStep('pre_payment')"
          @completed="emitLoad"
          @canceled="emitLoad"
          :user="user"
        />
      </div>

      <div v-else-if="action.type === 'takeover' && displayStep('takeover')">
        <loan-actions-takeover
          :action="action"
          :item="item"
          :open="isCurrentStep('takeover')"
          @completed="emitLoad"
          @canceled="emitLoad"
          :user="user"
        />
      </div>

      <div v-else-if="action.type === 'handover' && displayStep('handover')">
        <loan-actions-handover-self-service
          v-if="loanableIsSelfService"
          :action="action"
          :item="item"
          :open="isCurrentStep('handover')"
          @completed="emitLoad"
          @canceled="emitLoad"
          :user="user"
          @extension="$emit('extension')"
        />
        <loan-actions-handover
          v-else
          :action="action"
          :item="item"
          :open="isCurrentStep('handover')"
          @completed="emitLoad"
          @canceled="emitLoad"
          :user="user"
        />
      </div>

      <div v-else-if="action.type === 'payment' && displayStep('payment')">
        <loan-actions-payment
          :action="action"
          :item="item"
          :open="isCurrentStep('payment')"
          @completed="emitLoad"
          @canceled="emitLoad"
          :user="user"
        />
      </div>

      <loan-actions-extension
        :open="!action.executed_at"
        v-else-if="action.type === 'extension'"
        :action="action"
        :item="item"
        :user="user"
        @aborted="abortAction"
        @created="emitLoad"
        @completed="emitLoad"
        @canceled="emitLoad"
        @rejected="emitLoad"
      />

      <loan-actions-incident
        :open="!action.executed_at"
        v-else-if="action.type === 'incident'"
        :action="action"
        :item="item"
        :user="user"
        @aborted="abortAction"
        @created="emitLoad"
        @completed="emitLoad"
        @canceled="emitLoad"
      />
    </div>
  </div>
</template>

<script>
import LoanForm from "@/components/Loan/Form.vue";
import LoanActionsExtension from "@/components/Loan/Actions/Extension.vue";
import LoanActionsHandover from "@/components/Loan/Actions/Handover.vue";
import LoanActionsHandoverSelfService from "@/components/Loan/Actions/SelfService/HandoverSelfService.vue";
import LoanActionsIncident from "@/components/Loan/Actions/Incident.vue";
import LoanActionsIntention from "@/components/Loan/Actions/Intention.vue";
import LoanActionsPayment from "@/components/Loan/Actions/Payment.vue";
import LoanActionsPrePayment from "@/components/Loan/Actions/PrePayment.vue";
import LoanActionsTakeover from "@/components/Loan/Actions/Takeover.vue";

import LoanStepsSequence from "@/mixins/LoanStepsSequence";

export default {
  name: "Actions",
  mixins: [LoanStepsSequence],
  components: {
    LoanForm,
    LoanActionsExtension,
    LoanActionsHandover,
    LoanActionsHandoverSelfService,
    LoanActionsIncident,
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
      const indexOfAction = this.item.actions.indexOf(action);
      this.item.actions.splice(indexOfAction, 1);
    },
    emitLoad() {
      this.$emit("load");
    },
    emitSubmit() {
      this.$emit("submit");
    },
  },
};
</script>

<style lang="scss">
.loan-actions {
  .card {
    margin-bottom: 20px;
    box-shadow: 0 2px 5px $light-grey;
  }
}
</style>
