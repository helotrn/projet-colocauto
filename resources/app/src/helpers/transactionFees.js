// Keep in sync app/Services/StripeService.php
const cardFeeSpecs = {
  amex: {
    constant: 0,
    ratio: 0.035,
  },
  foreign: {
    constant: 0.3,
    ratio: 0.032,
  },
  default: {
    constant: 0.3,
    ratio: 0.022,
  },
};

const feeSpec = (card) => {
  if (!card) {
    return cardFeeSpecs.default;
  }

  if (card.credit_card_type === "American Express") {
    return cardFeeSpecs.amex;
  }
  if (card.country !== "CA") {
    return cardFeeSpecs.foreign;
  }
  return cardFeeSpecs.default;
};

// Passing fees on to customer:
// https://support.stripe.com/questions/passing-the-stripe-fee-on-to-customers
const addFeeToAmount = (amount, cardFeeSpec) => {
  return (amount + cardFeeSpec.constant) / (1 - cardFeeSpec.ratio);
};

export { cardFeeSpecs, feeSpec, addFeeToAmount };
