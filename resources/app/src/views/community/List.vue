<template>
  <layout-page name="community-list">
    <div v-if="routeDataLoaded">
      <b-row class="community-list__form">
        <b-col lg="3">
          <b-card>
            <loan-search-form :loan="loan"
              :selected-loanable-types="selectedLoanableTypes"
              @selectLoanableTypes="selectLoanableTypes"
              :loanable-types="loanableTypes"
              :form="loanForm"
              @reset="resetLoan"
              @submit="testLoanables" />
          </b-card>
        </b-col>

        <b-col v-if="!loading" lg="9">
          <b-row v-if="data.length > 0">
            <b-col v-for="loanable in data" :key="loanable.id" lg="4">
              <loanable-card v-bind="loanable" @select="selectLoanable(loanable)" />
            </b-col>
          </b-row>

          <b-row v-else>
            <b-col>Aucun véhicule ne correspond à ces critères</b-col>
          </b-row>
        </b-col>
        <layout-loading class="col-lg-9" v-else />
      </b-row>
    </div>
    <layout-loading v-else />
  </layout-page>
</template>

<script>
import LoanSearchForm from '@/components/Loan/SearchForm.vue';
import LoanableCard from '@/components/Loanable/Card.vue';

import Authenticated from '@/mixins/Authenticated';
import DataRouteGuards from '@/mixins/DataRouteGuards';
import ListMixin from '@/mixins/ListMixin';

import { buildComputed } from '@/helpers';

export default {
  name: 'CommunityList',
  mixins: [Authenticated, DataRouteGuards, ListMixin],
  components: {
    LoanSearchForm,
    LoanableCard,
  },
  data() {
    return {
      lastLoanMerged: false,
    };
  },
  mounted() {
    this.setSelectedLoanableTypes();
  },
  computed: {
    ...buildComputed('community.list', ['lastLoan', 'selectedLoanableTypes']),
    loan() {
      return this.$store.state.loans.item;
    },
    loanForm() {
      return this.$store.state.loans.form;
    },
    loanableForm() {
      return this.$store.state.loanables.form;
    },
    loanableTypes() {
      if (!this.loanableForm) {
        return [];
      }

      return this.loanableForm.general.type.options;
    },
  },
  methods: {
    resetLoan() {
      this.loan = { ...this.$store.state.loans.empty };
    },
    selectLoanableTypes(value) {
      this.selectedLoanableTypes = value.filter((item, i, ar) => ar.indexOf(item) === i);
    },
    async selectLoanable(loanable) {
      this.$store.commit('loans/patchItem', {
        borrower: this.user.borrower,
        community_id: this.user.communities[0].id,
        loanable,
        estimated_price: loanable.price,
      });

      this.$router.push('/loans/new');
    },
    setSelectedLoanableTypes() {
      this.$store.commit(`${this.slug}/setParam`, {
        name: 'type',
        value: this.selectedLoanableTypes.join(','),
      });
    },
    async testLoanables() {
      await this.$store.dispatch(`${this.slug}/test`, {
        loan: this.loan,
        communityId: this.user.communities[0].id,
      });
    },
  },
  watch: {
    loan: {
      deep: true,
      handler(val) {
        if (this.lastLoanMerged) {
          this.lastLoan = { ...val };
          this.$store.commit('loans/item', val);
        } else {
          this.lastLoanMerged = true;
          this.$store.commit('loans/item', {
            ...val,
            ...this.lastLoan,
          });
        }
      },
    },
    selectedLoanableTypes() {
      this.setSelectedLoanableTypes();
    },
  },
};
</script>

<style lang="scss">
.community-list {
  &__form {
    margin-bottom: 20px;
  }

  .page__content {
    padding-top: 45px;
    padding-bottom: 45px;
  }
}
</style>
