<template>
  <div class="payment-method-form">
    <validation-observer ref="observer" v-slot="{ passes }">
      <b-form class="form" @submit.prevent="passes(submit)">
        <div class="form__section" :class="{ 'd-none': definition.type.options.length < 2 }">
          <forms-builder
            :definition="definition"
            :disabled="!!paymentMethod.id"
            v-model="paymentMethod"
            entity="paymentMethods"
          />
        </div>

        <div
          :class="{
            'payment-method-form__credit-card': true,
            form__section: true,
            'd-none': paymentMethod.type !== 'credit_card',
          }"
        >
          <h2>{{ $t("types.credit_card") | capitalize }}</h2>
          <div>
            {{ $t("fees_notice") }}
            <ul>
              <li :class="{ 'font-weight-bold': cardType === 'amex' }">
                {{ $t("fee_types.amex") | capitalize }}&nbsp;:&nbsp;{{ fees.amex.ratio | percent }}
              </li>
              <li :class="{ 'font-weight-bold': cardType === 'foreign' }">
                {{ $t("fee_types.non_canadian") | capitalize }}&nbsp;:&nbsp;{{
                  fees.foreign.ratio | percent
                }}
                +
                {{ fees.foreign.constant | currency }}
              </li>
              <li :class="{ 'font-weight-bold': cardType === 'canadian' }">
                {{ $t("fee_types.canadian") | capitalize }}&nbsp;:&nbsp;{{
                  fees.default.ratio | percent
                }}
                +
                {{ fees.default.constant | currency }}
              </li>
            </ul>
          </div>
          <div v-if="!paymentMethod.id">
            <div id="stripe-card" class="form-control"></div>
            <b-form-invalid-feedback v-if="error">{{ error.message }}</b-form-invalid-feedback>
          </div>
        </div>

        <div
          class="payment-method-form__bank-account form__section"
          v-if="paymentMethod.type === 'bank_account'"
        >
          <h2>{{ $t("types.bank_account") | capitalize }}</h2>

          <forms-builder
            :definition="restOfDefinition"
            v-model="paymentMethod"
            entity="paymentMethods"
          />
        </div>

        <div class="form__buttons">
          <b-button
            variant="success"
            type="submit"
            :disabled="loading || error"
            class="mr-3"
            v-if="paymentMethod.type !== 'credit_card' || !paymentMethod.id"
          >
            {{ $t("forms.enregistrer") | capitalize }}
          </b-button>
          <b-button variant="danger" v-if="!!paymentMethod.id" @click="emitDestroy">
            {{ $t("forms.supprimer") | capitalize }}
          </b-button>
        </div>
      </b-form>
    </validation-observer>
  </div>
</template>

<script>
import { loadStripe } from "@stripe/stripe-js";

import FormsBuilder from "@/components/Forms/Builder.vue";
import { cardFeeSpecs } from "@/helpers/transactionFees";

import locales from "@/locales";

export default {
  name: "PaymentMethodForm",
  async mounted() {
    const element = document.getElementById("stripe-card");

    if (element) {
      const stripe = await loadStripe(process.env.VUE_APP_STRIPE_KEY);
      const card = stripe.elements({ locale: "fr" }).create("card", {
        hidePostalCode: true,
      });
      card.mount(element);

      this.card = card;
      this.stripe = stripe;
    }

    if (this.definition.type.options.length === 1) {
      this.paymentMethod.type = this.definition.type.options[0].value;
    }
  },
  components: {
    FormsBuilder,
  },
  props: {
    form: {
      type: Object,
      required: true,
    },
    loading: {
      type: Boolean,
      required: true,
    },
    paymentMethod: {
      type: Object,
      required: true,
    },
    user: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      card: null,
      stripe: null,
      error: null,
      fees: cardFeeSpecs,
    };
  },
  computed: {
    country() {
      return this.paymentMethod?.country;
    },
    brand() {
      return this.paymentMethod?.credit_card_type;
    },
    cardType() {
      if (!this.country || !this.brand) {
        return undefined;
      }
      if (this.brand === "American Express") {
        return "amex";
      }
      if (this.country !== "CA") {
        return "foreign";
      }
      return "canadian";
    },
    definition() {
      const { type } = this.form;
      return {
        type,
      };
    },
    restOfDefinition() {
      const { credit_card_type: creditCardType, external_id: externalId, name } = this.form;

      return {
        name,
        credit_card_type: creditCardType,
        external_id: externalId,
      };
    },
  },
  methods: {
    emitDestroy() {
      this.$emit("destroy");
    },
    async submit() {
      if (this.paymentMethod.type === "credit_card") {
        const { token, error } = await this.stripe.createToken(this.card, {
          name: `${this.user.full_name}`,
          address_country: "CA",
          address_line_1: `${this.user.address}`,
          address_zip: `${this.user.postal_code}`,
        });

        const { id, card } = token;

        this.paymentMethod.external_id = id;
        this.paymentMethod.credit_card_type = card.brand;
        this.paymentMethod.name = `${card.brand} se terminant par ${card.last4}`;
      }

      this.$emit("submit");
    },
  },

  i18n: {
    messages: {
      en: {
        ...locales.en.paymentMethods,
      },
      fr: {
        ...locales.fr.paymentMethods,
      },
    },
  },
};
</script>
