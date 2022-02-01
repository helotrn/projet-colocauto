<template>
  <layout-page :name="`community-view-${view}`" :loading="!routeDataLoaded" wide>
    <div :class="mainDivClasses">
      <b-row no-gutters>
        <b-col lg="3">
          <!-- loan search form -->
          <b-card :class="`community-view-${view}__form__sections`">
            <div :class="`community-view-${view}__form__sections__search`">
              <loan-search-form
                v-if="loan"
                :item="loan"
                :selected-loanable-types="selectedLoanableTypes"
                @selectLoanableTypes="selectLoanableTypes"
                @selectLoanable="selectLoanable"
                :loading="searching"
                :loanable-types="loanableTypes"
                :form="loanForm"
                :can-loan-car="canLoanCar"
                @changed="resetLoanables"
                @hide="searched = true"
                @reset="reset"
                @submit="testLoanables"
              />
            </div>
          </b-card>
          <!---->
          <!--results header for mobile view -->
          <b-card v-if="searched" :class="`community-view-${view}__form__toggler`">
            <p class="title">Résultats de votre recherche</p>
            <p class="description">Prochaine étape: vérifier la disponibilité!</p>
            <div class="button-display">
              <a class="btn-modify-search" @click="searched=false">Modifier votre recherche</a>
              <b-button v-if="view==='map'"pill class="btn-toggle" @click="gotoView('list')">
                Afficher la liste <svg-list />
              </b-button>
              <b-button v-else pill class="btn-toggle" @click="gotoView('map')">
                Afficher la carte <svg-map />
              </b-button>
            </div>
          </b-card>
          <!---->
        </b-col>
        <!-- button to switch to list view -->
        <b-col v-if="view==='map'" lg="9">
          <b-card class="button-container d-none d-lg-block">
            <b-button pill class="btn-toggle" @click="gotoView('list')">
              Afficher la liste <svg-list />
            </b-button>
          </b-card>
        </b-col>
        <!---->
        <b-col v-if="view === 'list' && searched" lg="9">
          <!-- results header for list view (large screens) -->
          <b-row no-gutters>
            <b-container :class="`community-view-${view}__form__toggler d-none d-lg-block`">
              <div class="button-display">
                <div>
                  <p class="title">Résultats de votre recherche</p>
                  <p class="description">Prochaine étape: vérifier la disponibilité!</p>
                </div>
                <b-button pill class="btn-toggle" @click="gotoView('map')">
                  Afficher la carte <svg-map />
                </b-button>
              </div>
            </b-container>
          </b-row>
          <!---->
          <!-- results for list view -->
          <b-row no-gutters>
            <community-list
              v-if="!loading"
              :data="data"
              :page="params.page"
              :per-page="params.per_page"
              :total="total"
              @page="setParam({ name: 'page', value: $event })"
              @select="selectLoanable"
              @test="testLoanable"
            />
            <layout-loading class="col-lg-9" v-else />
          </b-row>
          <!---->
        </b-col>
      </b-row>
    </div>
    <!-- map display -->
    <community-map
      v-if="view === 'map'"
      :data="data"
      :communities="user.communities"
      @test="testLoanable"
      @select="selectLoanable"
    />
    <!---->
  </layout-page>
</template>

<script>
import Authenticated from "@/mixins/Authenticated";
import DataRouteGuards from "@/mixins/DataRouteGuards";
import ListMixin from "@/mixins/ListMixin";
import UserMixin from "@/mixins/UserMixin";

import CommunityList from "@/components/Community/List.vue";
import CommunityMap from "@/components/Community/Map.vue";
import LoanSearchForm from "@/components/Loan/SearchForm.vue";

import ListIcon from "@/assets/svg/list.svg";
import MapIcon from "@/assets/svg/map.svg";

import { buildComputed } from "@/helpers";

export default {
  name: "CommunityView",
  mixins: [Authenticated, DataRouteGuards, ListMixin, UserMixin],
  components: {
    CommunityList,
    CommunityMap,
    LoanSearchForm,
    "svg-list": ListIcon,
    "svg-map": MapIcon,
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
      searching: false,
    };
  },
  beforeRouteEnter(to, from, next) {
    next((vm) => {
      if (vm.user.communities.filter((c) => !!c.approved_at && !c.suspended_at).length === 0) {
        vm.$store.commit("addNotification", {
          content: "Vous n'avez accès à aucun voisinage ou quartier.",
          title: "Accès refusé",
          variant: "warning",
          type: "loan",
        });
        vm.$router.replace("/app", {});
      }

      next();
    });
  },
  mounted() {
    if (!this.canLoanCar) {
      this.selectedLoanableTypes = this.selectedLoanableTypes.filter((t) => t !== "car");
    }
    this.resetPagination(this.view);
  },
  computed: {
    ...buildComputed("community.view", ["center", "lastLoan", "searched", "selectedLoanableTypes"]),
    mainDivClasses() {
      const base = `community-view-${this.view}__form`;
      return (
        base +
        (this.searched ? ` ${base}--searched` : "")
      );
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
        const car = types.find((t) => t.value === "car");
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
      const newLoan = { ...this.$store.state.loans.empty };
      delete newLoan.community;
      delete newLoan.community_id;

      this.$store.commit("loans/item", newLoan);

      this.selectedLoanableTypes = ["bike", "trailer"];

      if (this.canLoanCar) {
        this.selectedLoanableTypes.push("car");
      }
    },
    resetPagination(val) {
      this.setParam({ name: "page", value: 1 });
      switch (val) {
        case "list":
          this.setParam({ name: "per_page", value: 10 });
          break;
        case "map":
        default:
          this.setParam({ name: "per_page", value: 1000 });
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
      this.$store.commit("loans/patchItem", {
        borrower: {
          ...this.user.borrower,
          user: {
            id: this.user.id,
            full_name: this.user.full_name,
          },
        },
        borrower_id: this.user.borrower.id,
        loanable,
        loanable_id: loanable.id,
        estimated_insurance: loanable.insurance,
        estimated_price: loanable.price,
        platform_tip: (loanable.price === 0 ? 0 : Math.max(loanable.price * 0.1, 2)).toFixed(2),
      });

      this.$router.push("/loans/new");
    },
    async testLoanable(loanable) {
      await this.$store.dispatch(`${this.slug}/testOne`, {
        loanableId: loanable.id,
        loan: this.loan,
      });
    },
    async testLoanables() {
      this.searching = true;
      try {
        await this.$store.dispatch(`${this.slug}/testAll`, {
          loan: this.loan,
        });
      } finally {
        this.searching = false;
      }
      this.searched = true;
    },
  },
  watch: {
    loan: {
      deep: true,
      handler(val) {
        if (this.lastLoanMerged) {
          this.lastLoan = { ...val };
          delete this.lastLoan.community;
          delete this.lastLoan.community_id;

          this.$store.commit("loans/item", val);
        } else {
          this.lastLoanMerged = true;

          if (this.lastLoan) {
            if (this.$dayjs(this.lastLoan.departure_at).isAfter(this.$dayjs())) {
              const lastLoan = {
                ...val,
                ...this.lastLoan,
              };

              delete lastLoan.community;
              delete lastLoan.community_id;

              this.$store.commit("loans/item", lastLoan);
            }
          }
        }
      },
    },
    selectedLoanableTypes() {
      this.reloading = true;
      this.setParam({
        name: "type",
        value: this.selectedLoanableTypes.join(","),
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
  &-list,
  &-map {
    &__form {
      z-index: 20;
      position: relative;

      @include media-breakpoint-down(md) {
        &--searched &__sections.card {
          max-height: 0;
          overflow: hidden;

          + .card {
            max-height: 500px;
            background: $main-bg;
            border-radius: 0;
          }
        }
      }

      .card {
        pointer-events: all;

        &-body {
          padding: 0;
          margin: 1.25rem;
        }
        @include media-breakpoint-down(sm) {
          max-height: calc(100vh - #{$layout-navbar-height});
        }

        @include media-breakpoint-up(lg) {
          margin: 20px;
          max-height: 84vh;
        }
      }

      &__sections.card,
      &__sections.card + .card {
        transition: max-height $one-tick ease-in-out;
      }

      &__sections.card + .card {
        max-height: 0;
        overflow: hidden;
      }

      &__toggler {
        margin: 0;
        max-width: 100% !important;
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
          max-height: calc(100vh - #{$layout-navbar-height} - 30px);
          height: 100vh;
          overflow: auto;
          overflow-x: hidden;
        }
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
      pointer-events: none;
    }
  }

  &-list .page__content {
    padding: 0;
  }
}

.button-display {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 20px;
}

.card.button-container {
  position: absolute;
  margin: 15px 0;
  right: 0;
  background: none;
}

svg {
  margin-left: 5px;
}

.title {
  line-height: $h4-line-height;
  font-size: $h4-font-size;
  font-weight: 700;
  // margin-bottom: 25px;
}

@include media-breakpoint-up(lg) {
  .title {
    line-height: $h3-line-height;
    font-size: $h3-font-size;
  }
}
</style>
