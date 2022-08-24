<template>
  <div class="user-add-credit-box">
    <b-form-group
      label-cols="12"
      label="Montant à ajouter"
      description="* Approvisionnez davantage votre compte pour économiser sur les frais de transaction."
    >
      <b-form-radio-group
        class="d-none d-md-block"
        button-variant="outline-secondary"
        v-model="selectedAmount"
        :options="amounts"
        buttons
      />
      <b-form-select class="d-md-none" v-model="selectedAmount" :options="amounts" />
    </b-form-group>
    <b-row>
      <b-col md="8" xl="6">
        <b-form-group label="Montant Personnalisé" v-if="selectedAmount == 'other'">
          <b-form-input
            type="number"
            :min="normalizedMinimumRequired"
            :step="0.01"
            v-model="customAmount"
          />
        </b-form-group>
      </b-col>
    </b-row>

    <hr />
    <b-row v-if="amount > 0">
      <b-col sm="6">
        <strong>Montant Total Prélevé</strong>
        <p class="total">{{ amountWithFee | currency }}</p>
        <b-form-text
          >Incluant les frais de transaction&nbsp;:
          <pre>{{ this.feeRatio | percent }} + {{ this.feeConstant | currency }}</pre>
        </b-form-text>
      </b-col>
      <b-col sm="6">
        <strong>Choisir votre mode de paiement</strong>
        <b-form-select
          id="payment_method_id"
          name="payment_method_id"
          :options="paymentOptions"
          v-if="hasPaymentMethod"
          v-model="paymentMethodId"
        >
        </b-form-select>
        <div class="mt-1">
          <a href="/profile/payment_methods/new">
            {{ "ajouter un mode de paiement" | capitalize }}
          </a>
        </div>
      </b-col>
    </b-row>

    <b-row class="mt-3">
      <b-col class="text-center">
        <b-button
          class="mr-3"
          type="submit"
          variant="primary"
          @click="buyCredit"
          :disabled="amount <= 0 || amount < normalizedMinimumRequired || loading"
        >
          Ajouter les fonds
        </b-button>

        <b-button v-if="!noCancel" variant="outline-warning" @click="emitCancel">Annuler</b-button>
      </b-col>
    </b-row>
  </div>
</template>

<script>
import { currency } from "@/helpers/filters";

export default {
  name: "UserAddCreditBox",
  data() {
    return {
      customAmount:
        this.minimumRequired && this.minimumRequired > 0
          ? this.normalizeCurrency(this.minimumRequired) * 2
          : 20,
      feeRatio: 0.022,
      feeConstant: 0.3,
      loading: false,
      paymentMethodId: this.paymentMethods
        ? this.paymentMethods.find((p) => p.is_default)?.id
        : null,
      selectedAmount: this.tripCost
        ? this.normalizeCurrency(this.tripCost)
        : this.minimumRequired
        ? this.normalizeCurrency(this.minimumRequired)
        : this.addStandardOptions
        ? 10
        : "other",
    };
  },
  props: {
    // Minimum that needs to be paid.
    minimumRequired: {
      type: Number,
      required: false,
      default: 0,
    },
    // Estimated total cost of trip, including tip.
    tripCost: {
      type: Number,
      required: false,
      default: 0,
    },
    paymentMethods: {
      type: Array,
      required: false,
    },
    // This prop is a patch to remove cancel button from the pre-payment step.
    noCancel: {
      type: Boolean,
      required: false,
      default: false,
    },
    addStandardOptions: {
      type: Boolean,
      required: false,
      default: false,
    },
  },
  computed: {
    amount() {
      if (this.selectedAmount === "other") {
        const amount = parseFloat(this.customAmount);
        return isNaN(amount) ? 0 : amount;
      }

      return parseFloat(this.selectedAmount);
    },
    amountWithFee() {
      // Passing fees on to customer:
      // https://support.stripe.com/questions/passing-the-stripe-fee-on-to-customers
      return (this.amount + this.feeConstant) / (1 - this.feeRatio);
    },
    amounts() {
      const options = [];

      if (this.normalizedMinimumRequired > 0) {
        options.push({
          text: `Minimum (${currency(this.normalizedMinimumRequired)})`,
          value: this.normalizedMinimumRequired,
        });
      }

      if (this.normalizedTripCost > 0) {
        options.push({
          text: `Ce trajet (${currency(this.normalizedTripCost)})`,
          value: this.normalizedTripCost,
        });
      }

      if (this.addStandardOptions) {
        const standardOptions = [
          {
            text: "10$",
            value: 10,
          },
          {
            text: "20$",
            value: 20,
          },
          {
            text: "50$",
            value: 50,
          },
          {
            text: "100$",
            value: 100,
          },
        ];

        for (let i = 0, len = standardOptions.length; i < len; i += 1) {
          if (
            !this.normalizedMinimumRequired ||
            standardOptions[i].value > parseFloat(this.normalizedMinimumRequired)
          ) {
            options.push(standardOptions[i]);
          }
        }
      }
      options.push({
        text: "Autre *",
        value: "other",
      });

      return options;
    },
    normalizedMinimumRequired() {
      return this.normalizeCurrency(this.minimumRequired);
    },
    normalizedTripCost() {
      return this.normalizeCurrency(this.tripCost);
    },
    paymentOptions() {
      if (!this.hasPaymentMethod) {
        return [];
      }
      return this.paymentMethods.map((pm) => {
        const { name: text, id: value, is_default: selected } = pm;
        return {
          text,
          value,
          selected,
        };
      });
    },
    hasPaymentMethod() {
      return this.paymentMethods && this.paymentMethods.length > 0;
    },
  },
  methods: {
    emitCancel() {
      this.$emit("cancel");
    },
    normalizeCurrency(currency) {
      // Rounding to get rid of floating point errors
      const amount = Math.round(parseFloat(currency) * 100) / 100;
      return !isNaN(amount) && amount > 0 ? amount : 0;
    },
    async buyCredit() {
      this.loading = true;

      try {
        const { amount, paymentMethodId } = this;
        await this.$store.dispatch("account/buyCredit", {
          amount,
          paymentMethodId,
        });
        this.$emit("bought");
      } catch (e) {
        switch (e.response.data.message) {
          case "no_payment_method":
            this.$store.commit("addNotification", {
              content: "Vous n'avez pas configuré de mode de paiement.",
              title: "Mode de paiement par défaut manquant",
              variant: "warning",
              type: "payment_method",
            });
            break;
          default:
            break;
        }
      }

      this.loading = false;
    },
  },
  watch: {
    minimumRequired(newValue, oldValue) {
      if (this.selectedAmount === this.normalizeCurrency(oldValue)) {
        this.selectedAmount = this.normalizeCurrency(newValue);
      }
    },
    tripCost(newValue, oldValue) {
      if (this.selectedAmount === this.normalizeCurrency(oldValue)) {
        this.selectedAmount = this.normalizeCurrency(newValue);
      }
    },
  },
};
</script>

<style lang="scss">
.user-add-credit-box {
  .total {
    font-size: 40px;
  }
}
</style>
