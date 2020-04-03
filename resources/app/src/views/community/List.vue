<template>
  <layout-page name="community-list" :loading="!routeDataLoaded">
    <b-row class="community-list__form">
      <b-col lg="3">
        <b-card class="community-list__form__view mb-3 form-inline">
          <b-form-group label="Vue" label-for="view">
            <b-form-select v-model="view" name="view" id="view">
              <b-form-select-option value="list">Liste</b-form-select-option>
              <b-form-select-option value="map">Carte</b-form-select-option>
            </b-form-select>
          </b-form-group>
        </b-card>

        <b-card>
          <loan-search-form :loan="loan"
            :selected-loanable-types="selectedLoanableTypes"
            @selectLoanableTypes="selectLoanableTypes"
            :loanable-types="loanableTypes"
            :form="loanForm"
            @changed="resetLoanables"
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
      view: 'list',
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
    resetLoanables() {
      this.$store.dispatch(`${this.slug}/reset`);
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

          if (this.$dayjs(this.lastLoan.departure_at).isAfter(this.$dayjs())) {
            this.$store.commit('loans/item', {
              ...val,
              ...this.lastLoan,
            });
          }
        }
      },
    },
    selectedLoanableTypes() {
      this.setSelectedLoanableTypes();
    },
    view(newValue, oldValue) {
      if (newValue !== oldValue) {
        this.$router.push(`/commnity/${newValue}`);
      }
    },
  },
};
</script>

<style lang="scss">
.community-list {
  &__form {
    margin-bottom: 20px;

    &__view {
      .card-body {
        width: 100%;
      }

      .form-group {
        label {
          margin-right: 1em;
          display: inline-block;
        }

        > div {
          flex-grow: 1;
        }

        .custom-select {
          width: 100%;
        }
      }
    }
  }

  .page__content {
    padding-top: 45px;
    padding-bottom: 45px;
  }
}
</style>
