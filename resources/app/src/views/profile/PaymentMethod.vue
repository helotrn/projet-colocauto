<template>
  <b-container class="profile-payment-method" fluid v-if="item && routeDataLoaded">
    <vue-headful :title="fullTitle" />

    <b-row v-if="!!item.id">
      <b-col class="admin__buttons">
        <b-btn to="/profile/payment_methods/new">
          {{ $t('ajouter un autre mode de paiement') | capitalize }}
        </b-btn>
      </b-col>
    </b-row>

    <b-row>
      <b-col>
        <h1 v-if="item.name">{{ item.name }}</h1>
        <h1 v-else>
          <em>{{ $t('nouveau') | capitalize }} {{ $tc('model_name', 1) }}</em>
        </h1>
      </b-col>
    </b-row>

    <b-row>
      <b-col>
        <payment-method-form :payment-method="item" :form="form" :loading="loading"
          @submit="submitPaymentMethod" @destroy="destroy" :user="user" />
      </b-col>
    </b-row>
  </b-container>
  <layout-loading v-else />
</template>

<script>
import PaymentMethodForm from '@/components/PaymentMethod/PaymentMethodForm.vue';

import Authenticated from '@/mixins/Authenticated';
import DataRouteGuards from '@/mixins/DataRouteGuards';
import FormMixin from '@/mixins/FormMixin';
import UserMixin from '@/mixins/UserMixin';

import locales from '@/locales';

import { capitalize } from '@/helpers/filters';

export default {
  name: 'ProfilePaymentMethod',
  mixins: [Authenticated, DataRouteGuards, FormMixin, UserMixin],
  components: {
    PaymentMethodForm,
  },
  computed: {
    fullTitle() {
      const parts = [
        'LocoMotion',
        capitalize(this.$i18n.t('titles.profile')),
        capitalize(this.$i18n.tc('model_name', 2)),
      ];

      if (this.pageTitle) {
        parts.push(this.pageTitle);
      }

      return parts.reverse().join(' | ');
    },
    pageTitle() {
      return this.item.name || capitalize(this.$i18n.tc('model_name', 1));
    },
  },
  methods: {
    async submitPaymentMethod() {
      await this.submit();
      await this.$store.dispatch('loadUser');
    },
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.paymentMethods,
        ...locales.en.forms,
        profile: { ...locales.en.profile },
        titles: { ...locales.en.titles },
      },
      fr: {
        ...locales.fr.paymentMethods,
        ...locales.fr.forms,
        profile: { ...locales.fr.profile },
        titles: { ...locales.fr.titles },
      },
    },
  },
};
</script>

<style lang="scss">
</style>
