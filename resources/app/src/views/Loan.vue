<template>
  <layout-page name="loan" :loading="!(routeDataLoaded && item && loadedFullLoanable)">
    <vue-headful :title="fullTitle" />

    <loan-header :user="user" :loan="item" />

    <b-row>
      <b-col lg="3" class="loan__sidebar">
        <loan-menu :item="item" />
      </b-col>

      <b-col lg="9" class="loan__actions">
        <div class="loan__actions__buttons text-right mb-3" v-if="!!item.id">
          <b-button class="mr-3 mb-3" variant="primary" :disabled="hasReachedStep('takeover')">
            Modifier la réservation
          </b-button>
          <b-button class="mr-3 mb-3" variant="danger" :disabled="hasReachedStep('handover')">
            Annuler la réservation
          </b-button>
          <b-button class="mr-3 mb-3" variant="warning"
            :disabled="!hasReachedStep('takeover') || hasReachedStep('payment')">
            Signaler un retard
          </b-button>
          <b-button class="mb-3" variant="warning" :disabled="!hasReachedStep('pre_payment')">
            Signaler un incident
          </b-button>
        </div>

        <loan-actions :item="item" @load="loadItem" :form="loanForm" :user="user"
          @submit="submitLoan" />
      </b-col>
    </b-row>
  </layout-page>
</template>

<script>
import LoanActions from '@/components/Loan/Actions.vue';
import LoanHeader from '@/components/Loan/LoanHeader.vue';
import LoanMenu from '@/components/Loan/Menu.vue';

import Authenticated from '@/mixins/Authenticated';
import DataRouteGuards from '@/mixins/DataRouteGuards';
import FormMixin from '@/mixins/FormMixin';
import LoanStepsSequence from '@/mixins/LoanStepsSequence';

import { capitalize } from '@/helpers/filters';

export default {
  name: 'Loan',
  mixins: [Authenticated, DataRouteGuards, FormMixin, LoanStepsSequence],
  components: {
    LoanActions,
    LoanHeader,
    LoanMenu,
  },
  beforeRouteEnter(to, from, next) {
    next((vm) => {
      if (vm.id === 'new' && !vm.item.loanable) {
        vm.$router.replace('/community/list');
      }
    });
  },
  data() {
    return {
      loadedFullLoanable: false,
    };
  },
  computed: {
    loanForm() {
      return this.$store.state.loans.form;
    },
    fullTitle() {
      const parts = [
        'LocoMotion',
        capitalize(this.$i18n.tc('titles.loan', 2)),
      ];

      if (this.pageTitle) {
        parts.push(this.pageTitle);
      }

      return parts.reverse().join(' | ');
    },
    pageTitle() {
      return this.item.loanable ? this.item.loanable.name : capitalize(this.$i18n.tc('titles.loanable', 1));
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
    skipLoadItem() {
      if (this.id === 'new') {
        return true;
      }

      return false;
    },
    async submitLoan() {
      await this.submit();
      await this.$store.dispatch('loadUser');
    },
  },
};
</script>

<style lang="scss">
.loan.page {
  .page__content {
    padding-top: 45px;
    padding-bottom: 45px;
  }
}
</style>
