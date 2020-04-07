<template>
  <b-container fluid v-if="item && loadedFullLoanable">
    <vue-headful :title="fullTitle" />

    <loan-header :user="user" :loan="item" />

    <loan-actions :item="item" @load="loadItem" :form="form"
      :user="user" @submit="submit" />
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
  data() {
    return {
      loadedFullLoanable: false,
    };
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
  methods: {
    async formMixinCallback() {
      const { id, type } = this.item.loanable;
      await this.$store.dispatch(`${type}s/retrieveOne`, {
        params: {
          fields: '*,owner.id,owner.user.id,owner.user.avatar,owner.user.name',
          '!fields': 'events',
        },
        id,
      });
      const loanable = this.$store.state[`${type}s`].item;

      this.$store.commit(`${type}s/item`, null);

      this.$store.commit(`${this.slug}/mergeItem`, { loanable });

      this.loadedFullLoanable = true;
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
