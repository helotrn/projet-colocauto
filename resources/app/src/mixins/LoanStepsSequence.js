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
  },
  methods: {
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
          if (payment && payment.status === 'canceled') {
            return true;
          }
        case 'handover': // eslint-disable-line no-fallthrough
          if (handover && handover.status === 'canceled') {
            return true;
          }
        case 'takeover': // eslint-disable-line no-fallthrough
          if (takeover && takeover.status === 'canceled') {
            return true;
          }
        case 'pre_payment': // eslint-disable-line no-fallthrough
          if (prePayment && prePayment.status === 'canceled') {
            return true;
          }
        case 'intention': // eslint-disable-line no-fallthrough
          if (intention && intention.status === 'canceled') {
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
  },
};
