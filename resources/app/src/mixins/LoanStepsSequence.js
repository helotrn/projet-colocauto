export default {
  computed: {
    hasActiveExtensions() {
      if (!this.item.id) {
        return false;
      }

      return this.item.extensions
        .reduce((acc, i) => (acc || !i.id || i.status !== 'completed'), false);
    },
    hasActiveIncidents() {
      if (!this.item.id) {
        return false;
      }

      return this.item.incidents.reduce((acc, i) => (acc || i.status !== 'completed'), false);
    },
    isOwnedLoanable() {
      return !!this.item.loanable.owner;
    },
    /*
      For the time being, a loanable is self-service if it has no owner.
      This definition is likely to change.
    */
    loanableIsSelfService() {
      // If the loanable has no owner (Considered as belonging to
      // the community, hence self-service)
      return !this.item.loanable.owner;
    },
    loanIsCanceled() {
      return !!this.item.canceled_at;
    },
    userIsOwner() {
      if (!this.item.loanable.owner) {
        return false;
      }

      return this.user.id === this.item.loanable.owner.user.id;
    },
    borrowerIsOwner() {
      // If no owner, then false.
      if (!this.item.loanable.owner) {
        return false;
      }

      // Otherwise, is the borrower the owner?
      return this.item.borrower.user.id === this.item.loanable.owner.user.id;
    },
  },
  methods: {
    addExtension() {
      const handover = this.item.actions.find(a => a.type === 'handover');

      if (handover) {
        const indexOfHandover = this.item.actions.indexOf(handover);
        this.item.actions.splice(indexOfHandover, 0, {
          status: 'in_process',
          new_duration: this.item.actual_duration_in_minutes,
          comments_on_extension: '',
          type: 'extension',
          loan_id: this.item.id,
        });
      }

      setTimeout(() => {
        const el = document.getElementById('loan-extension-new');
        this.$scrollTo(el);
      }, 10);
    },
    addIncident(type) {
      const handover = this.item.actions.find(a => a.type === 'handover');

      if (handover) {
        const indexOfHandover = this.item.actions.indexOf(handover);
        this.item.actions.splice(indexOfHandover, 0, {
          status: 'in_process',
          incident_type: type,
          type: 'incident',
          loan_id: this.item.id,
        });
      }

      setTimeout(() => {
        const el = document.getElementById('loan-incident-new');
        this.$scrollTo(el);
      }, 10);
    },
    async cancelLoan() {
      await this.$store.dispatch('loans/cancel', this.item.id);
      await this.loadItemAndUser();
    },
    hasCanceledStep(step) {
      const { actions } = this.item;
      const intention = actions.find(a => a.type === 'intention');
      const prePayment = actions.find(a => a.type === 'pre_payment');
      const takeover = actions.find(a => a.type === 'takeover');
      const handover = actions.find(a => a.type === 'handover');
      const payment = actions.find(a => a.type === 'payment');

      // Using fallthrough because an earlier step cancels
      // all following steps if it has been canceled
      switch (step) {
        case 'payment': // eslint-disable-line no-fallthrough
          if (handover?.status === 'canceled' && payment?.status !== 'canceled'
            && !this.loanIsCanceled) {
            return false;
          }

          if ((payment?.status === 'canceled')
              || (payment?.status === 'in_process' && this.loanIsCanceled)) {
            return true;
          }
        case 'handover': // eslint-disable-line no-fallthrough
          if (takeover?.status === 'canceled' && handover?.status !== 'canceled'
            && !this.loanIsCanceled) {
            return false;
          }

          if ((handover?.status === 'canceled')
              || (handover?.status === 'in_process' && this.loanIsCanceled)) {
            return true;
          }
        case 'takeover': // eslint-disable-line no-fallthrough
          if ((takeover?.status === 'canceled')
              || (takeover?.status === 'in_process' && this.loanIsCanceled)) {
            return true;
          }
        case 'pre_payment': // eslint-disable-line no-fallthrough
          if ((prePayment?.status === 'canceled')
              || (prePayment?.status === 'in_process' && this.loanIsCanceled)) {
            return true;
          }
        case 'intention': // eslint-disable-line no-fallthrough
          if ((intention?.status === 'canceled')
              || (intention?.status === 'in_process' && this.loanIsCanceled)) {
            return true;
          }
          break;
        default:
          return false;
      }

      return false;
    },
    hasReachedStep(step) {
      const { id, actions } = this.item;
      const intention = actions.find(a => a.type === 'intention');
      const prePayment = actions.find(a => a.type === 'pre_payment');
      const takeover = actions.find(a => a.type === 'takeover');
      const handover = actions.find(a => a.type === 'handover');
      const payment = actions.find(a => a.type === 'payment');

      switch (step) {
        case 'creation':
          return !!id;
        case 'intention':
          return intention && !!intention.executed_at;
        case 'pre_payment':
          return prePayment && !!prePayment.executed_at;
        case 'takeover':
          return takeover && !!takeover.executed_at;
        case 'handover':
          return handover && !!handover.executed_at;
        case 'payment':
          return payment && !!payment.executed_at;
        default:
          return false;
      }
    },
    isCurrentStep(step) {
      const { id, actions } = this.item;
      const intention = actions.find(a => a.type === 'intention');
      const prePayment = actions.find(a => a.type === 'pre_payment');
      const takeover = actions.find(a => a.type === 'takeover');
      const handover = actions.find(a => a.type === 'handover');
      const payment = actions.find(a => a.type === 'payment');

      switch (step) {
        case 'creation':
          return !id;
        case 'intention':
          return intention && !intention.executed_at;
        case 'pre_payment':
          return prePayment && !prePayment.executed_at;
        case 'takeover':
          return takeover && !takeover.executed_at;
        case 'handover':
          return handover && !handover.executed_at;
        case 'payment':
          return payment && !payment.executed_at;
        default:
          return false;
      }
    },
    /*
      This method determines whether a loan step should be displayed.

      Visible steps should only depend on the loanable, who's involved in the
      loan and the loan itself.
      They should not depend on who the current user is.
    */
    displayStep(step) {
      switch (step) {
        case 'intention':
          // Intention is required if loanable is not self-service
          // As of now, it is required even if the borrower is the owner.
          // This is likely to be reviewed.
          return !this.loanableIsSelfService;

        case 'pre_payment':
          // Pre-payment should be displayed whenever the loan has an estimated
          // cost over 0. This includes free loans for which the platform tip
          // is greater than 0.
          return parseFloat(this.item.estimated_price) > 0
            || parseFloat(this.item.estimated_insurance) > 0
            || parseFloat(this.item.platform_tip) > 0;

        case 'takeover':
        case 'handover':
          // Takeover and handover steps are always displayed.
          return true;

        case 'payment':
          // Payment should be displayed when the loan is not inherently free.
          // As of now, this is when the loanable is not self service.
          // Show it anytime the final price is > 0 for whatever reason.
          return !this.loanableIsSelfService
            || this.item.final_price > 0;

        default:
          return false;
      }
    },
  },
};
