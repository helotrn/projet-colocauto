<template>
  <layout-page :name="`community-view-${view}`" :loading="!routeDataLoaded && view !== 'map'"
    :wide="view === 'map'">
    <div :class="`community-view-${view}__form${view === 'map' ? ' container' : ''}`">
      <b-row>
        <b-col lg="3">
          <b-card>
            <div :class="`community-view-${view}__form__view mb-3 form-inline`">
              <b-form-group label="Vue" label-for="view">
                <b-form-select :value="view" @change="gotoView" name="view" id="view">
                  <b-form-select-option value="list">Liste</b-form-select-option>
                  <b-form-select-option value="map">Carte</b-form-select-option>
                </b-form-select>
              </b-form-group>
            </div>

            <hr>

            <div class="`community-view-${view}__form__search`">
              <loan-search-form v-if="loan" :loan="loan"
                :selected-loanable-types="selectedLoanableTypes"
                @selectLoanableTypes="selectLoanableTypes"
                @selectLoanable="selectLoanable"
                :loanable-types="loanableTypes"
                :form="loanForm"
                @changed="resetLoanables"
                @reset="reset"
                @submit="testLoanables" />
            </div>
          </b-card>
        </b-col>

        <b-col v-if="view === 'list'" lg="9">
          <community-list v-if="!loading" :data="data"
            @select="selectLoanable" @test="testLoanable" />
          <layout-loading class="col-lg-9" v-else />
        </b-col>
      </b-row>
    </div>

    <community-map v-if="view === 'map'" :data="data" :communities="user.communities"
      @test="testLoanable" @select="selectLoanable" />
  </layout-page>
</template>

<script>
import Authenticated from '@/mixins/Authenticated';
import DataRouteGuards from '@/mixins/DataRouteGuards';
import ListMixin from '@/mixins/ListMixin';

import CommunityList from '@/components/Community/List.vue';
import CommunityMap from '@/components/Community/Map.vue';
import LoanSearchForm from '@/components/Loan/SearchForm.vue';

import { buildComputed } from '@/helpers';

export default {
  name: 'CommunityView',
  mixins: [Authenticated, DataRouteGuards, ListMixin],
  components: {
    CommunityList,
    CommunityMap,
    LoanSearchForm,
  },
  props: {
    view: {
      type: String,
      required: true,
    },
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
    ...buildComputed('community.view', ['center', 'lastLoan', 'selectedLoanableTypes']),
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
    gotoView(view) {
      this.$router.push(`/community/${view}`);
    },
    reset() {
      this.$store.commit('loans/item', { ...this.$store.state.loans.empty });
      this.selectedLoanableTypes = ['bike', 'trailer', 'car'];
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
    async testLoanable(loanable) {
      await this.$store.dispatch(`${this.slug}/testOne`, {
        loanableId: loanable.id,
        loan: this.loan,
        communityId: this.user.communities[0].id,
      });
    },
    async testLoanables() {
      await this.$store.dispatch(`${this.slug}/testAll`, {
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
  },
};
</script>

<style lang="scss">
.community-view {
  &-list, &-map {
    &__form {
      z-index: 20;
      position: relative;

      &__view {
        .card-body {
          width: 100%;
        }

        .form-group label {
          margin-right: 1em;
          display: inline-block;
        }

        .form-group > div {
          flex-grow: 1;
        }

        .form-group .custom-select {
          width: 100%;
        }
      }
    }
  }

  &-map {
    .page__content {
      position: relative;
    }

    .community-map {
      position: absolute;
      top: 0;
      left: 0;
      width: 100vw;
      height: calc(100vh - #{$layout-navbar-height + $molotov-footer-height});
      z-index: 10;
    }

    &__form {
      padding-top: 45px;
      padding-bottom: 45px;
      pointer-events: none;

      .card {
        pointer-events: all;

        &-body {
          padding: 0;
          margin: 1.25rem;
        }

        max-height: calc(100vh - #{$layout-navbar-height + $molotov-footer-height} - 92px);
      }
    }
  }

  &-list .page__content {
    padding-top: 45px;
    padding-bottom: 45px;
  }
}
</style>
