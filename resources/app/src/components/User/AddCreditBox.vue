<template>
  <div class="user-add-credit-box">
    <b-row>
      <b-col lg="8" class="user-add-credit-box__add">
        <p>Ajouter&nbsp;:</p>

        <p><b-form-radio-group v-model="selectedAmount" :options="amounts" buttons /></p>

        <p v-if="selectedAmount === 'other'" class="user-add-credit-box__add__custom">
          <b-form-input type="number" :min="minimumRequired" :step="0.01" v-model="customAmount" />
        </p>

        <p>Montant prélevé du mode de paiement&nbsp;: {{ amountWithFee | currency }}</p>

        <p><small>Frais de transaction&nbsp;: 2,2% + 30¢</small></p>
      </b-col>

      <b-col lg="4" class="user-add-credit-box__balance">
        <p>Solde</p>

        <p class="user-add-credit-box__balance__initial">{{ user.balance | currency }}</p>
      </b-col>
    </b-row>

    <b-row>
      <b-col lg="6" class="user-add-credit-box__payment-methods">
        <b-form-select
          id="payment_method_id"
          name="payment_method_id"
          :options="paymentMethods"
          v-if="hasPaymentMethod"
          v-model="paymentMethodId"
        >
          <template v-slot:first>
            <b-form-select-option :value="null" disabled>
              Choisir votre mode de paiement
            </b-form-select-option>
          </template>
        </b-form-select>
        <b-btn v-else to="/profile/payment_methods/new">
          {{ $t("ajouter un mode de paiement") | capitalize }}
        </b-btn>
      </b-col>

      <b-col class="user-add-credit-box__explanations">
        <p>Approvisionnez votre compte pour économiser sur les frais de transaction.</p>
      </b-col>
    </b-row>

    <b-row class="user-add-credit-box__buttons" tag="p">
      <b-col class="text-center">
        <b-button
          class="mr-3"
          type="submit"
          variant="primary"
          @click="buyCredit"
          :disabled="amount < floatMinimumRequired || loading"
        >
          Valider
        </b-button>

        <b-button variant="outline-warning" @click="emitCancel">Annuler</b-button>
      </b-col>
    </b-row>
  </div>
</template>

<script>
export default {
  name: "UserAddCreditBox",
  data() {
    return {
      customAmount: this.minimumRequired ? parseFloat(this.minimumRequired) * 2 : 20,
      feeRatio: 0.022,
      feeConstant: 0.3,
      loading: false,
      paymentMethodId: this.user.payment_methods.reduce((acc, p) => {
        if (p.is_default) {
          return p.id;
        }

        return acc;
      }, null),
      selectedAmount: this.minimumRequired ? parseFloat(this.minimumRequired) : 10,
    };
  },
  props: {
    minimumRequired: {
      type: Number,
      required: false,
      default: 0,
    },
    user: {
      type: Object,
      required: true,
    },
  },
  computed: {
    amount() {
      if (this.selectedAmount === "other") {
        return parseFloat(this.customAmount, 10);
      }

      return parseFloat(this.selectedAmount, 10);
    },
    amountWithFee() {
      // Passing fees on to customer:
      // https://support.stripe.com/questions/passing-the-stripe-fee-on-to-customers
      return (this.amount + this.feeConstant) / (1 - this.feeRatio);
    },
    amounts() {
      const options = [];

      if (this.minimumRequired) {
        options.push({
          text: "Je paie juste pour ce trajet",
          value: this.floatMinimumRequired,
        });
      }

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
          !this.minimumRequired ||
          standardOptions[i].value > parseFloat(this.minimumRequired, 10)
        ) {
          options.push(standardOptions[i]);
        }
      }

      options.push({
        text: "J'en profite pour ajouter...",
        value: "other",
      });

      return options;
    },
    floatMinimumRequired() {
      return parseFloat(this.minimumRequired);
    },
    paymentMethods() {
      return this.user.payment_methods.map((pm) => {
        const { name: text, id: value, is_default: selected } = pm;
        return {
          text,
          value,
          selected,
        };
      });
    },
    hasPaymentMethod() {
      return this.user.payment_methods.length > 0;
    },
  },
  methods: {
    emitCancel() {
      this.$emit("cancel");
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
};
</script>

<style lang="scss">
.user-add-credit-box {
  &__add label {
    display: flex;
    flex-direction: column;
    justify-content: center;
  }

  &__add__custom {
    margin-right: 70px;
    margin-left: 70px;
    text-align: center;
  }

  &__balance {
    &__initial,
    &__added {
      font-size: 24px;
      font-weight: bold;
    }
  }
}
</style>
