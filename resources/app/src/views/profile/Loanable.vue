<template>
  <b-container fluid v-if="item">
    <vue-headful :title="fullTitle" />

    <b-row>
      <b-col>
        <h1 v-if="item.name">{{ item.name }}</h1>
        <h1 v-else><em>{{ $t('nouveau') | capitalize }} {{ $tc('véhicule', 1) }}</em></h1>
      </b-col>
    </b-row>

    <b-row>
      <b-col>
        <loanable-form :loanable="item" :form="form" :loading="loading" @submit="submit" />
      </b-col>
    </b-row>
  </b-container>
</template>

<script>
import LoanableForm from '@/components/Loanable/Form.vue';

import FormMixin from '@/mixins/FormMixin';

import locales from '@/locales';

import { capitalize } from '@/helpers/filters';

export default {
  name: 'ProfileLoanable',
  mixins: [FormMixin],
  components: {
    LoanableForm,
  },
  computed: {
    fullTitle() {
      const parts = [
        'LocoMotion',
        capitalize(this.$i18n.t('titles.profile')),
        capitalize(this.$i18n.tc('véhicule', 2)),
      ];

      if (this.pageTitle) {
        parts.push(this.pageTitle);
      }

      return parts.reverse().join(' | ');
    },
    pageTitle() {
      return this.item.name || capitalize(this.$i18n.tc('véhicule', 1));
    },
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.loanables,
        ...locales.en.forms,
        profile: { ...locales.en.profile },
        titles: { ...locales.en.titles },
      },
      fr: {
        ...locales.fr.loanables,
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
