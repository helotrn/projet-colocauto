<template>
  <div class="payment-method-form">
    <validation-observer ref="observer" v-slot="{ passes }">
      <b-form class="form" @submit.prevent="passes(submit)">
        <div class="form__section">
          <forms-builder :definition="definition" :disabled="!!paymentMethod.id"
            v-model="paymentMethod" entity="paymentMethods" />
        </div>

        <div v-if="!paymentMethod.id" :class="{
          'payment-method-form__credit-card': true,
          'form__section': true,
          'd-none': paymentMethod.type !== 'credit_card',
        }">
          <h2>Carte de crédit</h2>

          <div id="stripe-card" class="form-control" />
        </div>

        <div class="payment-method-form__bank-account form__section"
          v-if="paymentMethod.type === 'bank_account'">
          <h2>Compte bancaire</h2>

          <forms-builder :definition="restOfDefinition"
            v-model="paymentMethod" entity="paymentMethods" />
        </div>

        <div class="form__buttons">
          <b-button variant="danger" v-if="!!paymentMethod.id" @click="emitDestroy">
            {{ $t('forms.supprimer') | capitalize }}
          </b-button>
          <b-button variant="success" type="submit" :disabled="loading" v-else>
            {{ $t('forms.enregistrer') | capitalize }}
          </b-button>
        </div>
      </b-form>
    </validation-observer>
  </div>
</template>

<script>
import { loadStripe } from '@stripe/stripe-js';

import FormsBuilder from '@/components/Forms/Builder.vue';

export default {
  name: 'PaymentMethodForm',
  async mounted() {
    const element = document.getElementById('stripe-card');

    if (element) {
      const stripe = await loadStripe(process.env.VUE_APP_STRIPE_KEY);
      const card = stripe.elements({ locale: 'fr' }).create(
        'card',
        {
          hidePostalCode: true,
        },
      );
      card.mount(element);

      this.card = card;
      this.stripe = stripe;
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
    };
  },
  computed: {
    definition() {
      const { type } = this.form;
      return {
        type,
      };
    },
    restOfDefinition() {
      const {
        credit_card_type: creditCardType,
        external_id: externalId,
        name,
      } = this.form;

      return {
        name,
        credit_card_type: creditCardType,
        external_id: externalId,
      };
    },
  },
  methods: {
    emitDestroy() {
      this.$emit('destroy');
    },
    async submit() {
      const { token, error } = await this.stripe.createToken(this.card, {
        name: `${this.user.full_name}`,
        address_country: 'CA',
        address_line_1: `${this.user.address}`,
        address_zip: `${this.user.postal_code}`,
      });

      if (error) {
        this.$store.commit('addNotification', {
          content: 'Les informations de la carte sont incomplètes ou invalides.',
          title: 'Erreur sur la carte',
          variant: 'danger',
          type: 'payment-form',
        });
      }

      const { id, card } = token;

      this.paymentMethod.external_id = id;
      this.paymentMethod.credit_card_type = card.brand;
      this.paymentMethod.name = `${card.brand} se terminant par ${card.last4}`;

      this.$emit('submit');
    },
  },
};
</script>

<style lang="scss">
</style>
