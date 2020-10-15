<template>
  <layout-page :name="`community-view-${view}`" :loading="!routeDataLoaded"
    :wide="view === 'map'">
    <div :class="mainDivClasses">
      <b-row>
        <b-col lg="3">
          <b-card :class="`community-view-${view}__form__sections`">
            <div :class="`community-view-${view}__form__sections__view mb-3`">
              <b-form-group label="Vue" label-for="view">
                <b-form-select :value="view" @change="gotoView" name="view" id="view">
                  <b-form-select-option value="list">Liste</b-form-select-option>
                  <b-form-select-option value="map">Carte</b-form-select-option>
                </b-form-select>
              </b-form-group>
            </div>

            <hr>

            <div :class="`community-view-${view}__form__sections__search`">
              <loan-search-form v-if="loan" :item="loan"
                :selected-loanable-types="selectedLoanableTypes"
                @selectLoanableTypes="selectLoanableTypes"
                @selectLoanable="selectLoanable"
                :loanable-types="loanableTypes"
                :form="loanForm"
                :can-loan-car="canLoanCar"
                @changed="resetLoanables"
                @hide="searched = true"
                @reset="reset"
                @submit="testLoanables" />
            </div>
          </b-card>
          <b-card :class="`community-view-${view}__form__toggler`">
            <b-button @click="searched = false">Modifier la recherche</b-button>
          </b-card>
        </b-col>

        <b-col v-if="view === 'list'" lg="9">
          <community-list v-if="!loading" :data="data"
            :page="params.page" :per-page="params.per_page" :total="total"
            @page="setParam({ name: 'page', value: $event })"
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
import UserMixin from '@/mixins/UserMixin';

import CommunityList from '@/components/Community/List.vue';
import CommunityMap from '@/components/Community/Map.vue';
import LoanSearchForm from '@/components/Loan/SearchForm.vue';

import { buildComputed } from '@/helpers';

export default {
  name: 'CommunityView',
  mixins: [Authenticated, DataRouteGuards, ListMixin, UserMixin],
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
      reloading: false,
    };
  },
  beforeRouteEnter(to, from, next) {
    next((vm) => {
      if (vm.user.communities.filter(c => !!c.approved_at && !c.suspended_at).length === 0) {
        vm.$store.commit('addNotification', {
          content: "Vous n'avez accès à aucun voisinage ou quartier.",
          title: 'Accès refusé',
          variant: 'warning',
          type: 'loan',
        });
        vm.$router.replace('/app', {});
      }

      next();
    });
  },
  mounted() {
    if (!this.canLoanCar) {
      this.selectedLoanableTypes = this.selectedLoanableTypes.filter(t => t !== 'car');
    }
    this.resetPagination(this.view);
  },
  computed: {
    ...buildComputed('community.view', [
      'center',
      'lastLoan',
      'searched',
      'selectedLoanableTypes',
    ]),
    mainDivClasses() {
      const base = `community-view-${this.view}__form`;
      return base + (this.searched ? ` ${base}--searched` : '')
        + (this.view === 'map' ? ' container' : '');
    },
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

      const types = this.loanableForm.general.type.options;

      if (!this.canLoanCar) {
        const car = types.find(t => t.value === 'car');
        car.disabled = true;
      }

      return types;
    },
  },
  methods: {
    gotoView(view) {
      this.$router.push(`/community/${view}`);
    },
    reset() {
      this.$store.commit('loans/item', { ...this.$store.state.loans.empty });

      this.selectedLoanableTypes = ['bike', 'trailer'];

      if (this.canLoanCar) {
        this.selectedLoanableTypes.push('car');
      }
    },
    resetPagination(val) {
      this.setParam({ name: 'page', value: 1 });
      switch (val) {
        case 'list':
          this.setParam({ name: 'per_page', value: 10 });
          break;
        case 'map':
        default:
          this.setParam({ name: 'per_page', value: 1000 });
          break;
      }
    },
    resetLoanables() {
      this.$store.dispatch(`${this.slug}/reset`);
    },
    selectLoanableTypes(value) {
      this.selectedLoanableTypes = value.filter((item, i, ar) => ar.indexOf(item) === i);
    },
    async selectLoanable(loanable) {
      this.$store.commit('loans/patchItem', {
        borrower: {
          ...this.user.borrower,
          user: {
            id: this.user.id,
            full_name: this.user.full_name,
          },
        },
        borrower_id: this.user.borrower.id,
        community_id: this.user.communities[0].id,
        loanable,
        loanable_id: loanable.id,
        estimated_insurance: loanable.insurance,
        estimated_price: loanable.price,
        platform_tip: (loanable.price === 0 ? 0 : Math.max(loanable.price * 0.1, 2)).toFixed(2),
      });

      this.$router.push('/loans/new');
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
      this.searched = true;
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

          if (this.lastLoan) {
            if (this.$dayjs(this.lastLoan.departure_at).isAfter(this.$dayjs())) {
              this.$store.commit('loans/item', {
                ...val,
                ...this.lastLoan,
              });
            }
          }
        }
      },
    },
    selectedLoanableTypes() {
      this.reloading = true;
      this.setParam({
        name: 'type',
        value: this.selectedLoanableTypes.join(','),
      });
    },
    view(val) {
      this.resetPagination(val);
    },
  },
};
</script>

<style lang="scss">
@import "~bootstrap/scss/mixins/breakpoints";

.community-view {
  &-list, &-map {
    &__form {
      z-index: 20;
      position: relative;

      @include media-breakpoint-down(lg) {
        &--searched &__sections.card {
          max-height: 0;
          overflow: hidden;

          + .card {
            max-height: 100px;
          }
        }
      }

      &__sections.card, &__sections.card + .card {
        transition: max-height $one-tick ease-in-out;
      }

      &__sections.card + .card {
        max-height: 0;
        overflow: hidden;
      }

      &__toggler.card {
        display: inline-block;
      }

      &__sections__view {
        .card-body {
          width: 100%;
          display: flex;
          flex-direction: column;
        }

        .form-group {
          margin-bottom: 0;
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
      width: 100%;
      height: calc(100vh - #{$layout-navbar-height});
      z-index: 10;
    }

    &__form {
      max-height: calc(100vh - #{$layout-navbar-height} - 1px);

      padding-top: 15px;
      padding-bottom: 15px;

      @include media-breakpoint-up(lg) {
        padding-top: 45px;
        padding-bottom: 45px;
      }
      pointer-events: none;

      &__sections.card {
        min-width: 382px;
      }

      .card {
        min-width: 382px;

        pointer-events: all;

        &-body {
          padding: 0;
          margin: 1.25rem;
        }

        max-height: calc(100vh - #{$layout-navbar-height} - 30px);
      }

      &__sections {
        &__view {
          height: 34px;

          .form-group {
            display: flex;
          }

          .form-group label {
            line-height: 34px;
          }
        }

        hr {
          height: 1px;
        }

        &__search {
          max-height: calc(100vh - #{$layout-navbar-height} - 154px);
          overflow: auto;
          overflow-x: hidden;
        }
      }
    }
  }

  &-list .page__content {
    padding-top: 45px;
    padding-bottom: 45px;
  }
}
</style>
