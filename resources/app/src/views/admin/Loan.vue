<template>
  <b-container fluid v-if="item">
    <vue-headful :title="fullTitle" />

    <loan-header :user="user" :loan="item" />

    <loan-actions :item="item" @load="loadItem" :form="form" :user="user"
      @submit="submit" />
  </b-container>
  <layout-loading v-else />
</template>

<script>
import LoanActions from '@/components/Loan/Actions.vue';
import LoanHeader from '@/components/Loan/LoanHeader.vue';

import Authenticated from '@/mixins/Authenticated';
import FormMixin from '@/mixins/FormMixin';
import LoanStepsSequence from '@/mixins/LoanStepsSequence';

import locales from '@/locales';

import { capitalize } from '@/helpers/filters';

export default {
  name: 'AdminLoan',
  mixins: [Authenticated, FormMixin, LoanStepsSequence],
  components: {
    LoanActions,
    LoanHeader,
  },
  computed: {
    fullTitle() {
      const parts = [
        'LocoMotion',
        capitalize(this.$i18n.t('titles.admin')),
        capitalize(this.$i18n.tc('emprunt', 2)),
      ];

      if (this.pageTitle) {
        parts.push(this.pageTitle);
      }

      return parts.reverse().join(' | ');
    },
    pageTitle() {
      return this.item.name || capitalize(this.$i18n.tc('emprunt', 1));
    },
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.loans,
        ...locales.en.forms,
        titles: locales.en.titles,
      },
      fr: {
        ...locales.fr.loans,
        ...locales.fr.forms,
        titles: locales.fr.titles,
      },
    },
  },
};
</script>

<style lang="scss">
</style>
