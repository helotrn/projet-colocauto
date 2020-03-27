export default {
  methods: {
    hasCanceledStep(step) {
      const { actions } = this.item;
      const intention = actions.find(a => a.type === 'intention');
      const prePayment = actions.find(a => a.type === 'pre_payment');
      const takeover = actions.find(a => a.type === 'takeover');
      const handover = actions.find(a => a.type === 'handover');
      const payment = actions.find(a => a.type === 'payment');

      let canceled = true;
      switch (step) {
        case 'payment': // eslint-disable-line no-fallthrough
          canceled = canceled && payment && payment.status === 'canceled';
        case 'handover': // eslint-disable-line no-fallthrough
          canceled = canceled && handover && handover.status === 'canceled';
        case 'takeover': // eslint-disable-line no-fallthrough
          canceled = canceled && takeover && takeover.status === 'canceled';
        case 'pre_payment': // eslint-disable-line no-fallthrough
          canceled = canceled && prePayment && prePayment.status === 'canceled';
        case 'intention': // eslint-disable-line no-fallthrough
          canceled = canceled && intention && intention.status === 'canceled';
          break;
        default:
          return false;
      }

      return canceled;
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
          return intention && intention.executed_at;
        case 'pre_payment':
          return prePayment && prePayment.executed_at;
        case 'takeover':
          return takeover && takeover.executed_at;
        case 'handover':
          return handover && handover.executed_at;
        case 'payment':
          return payment && payment.executed_at;
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
