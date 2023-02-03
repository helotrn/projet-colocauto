<template>
  <layout-page
    :name="`community-view community-view--${view}`"
    :class="isSearched"
    :loading="!routeDataLoaded"
    fluid
  >
    <div :class="`community-view__overlay ${isMap}`">
      <b-row>
        <b-col lg="4" xl="3">
          <!-- loan search form container -->
          <b-card class="community-view__search-container">
            <div class="community-view__search-menu">
              <loan-search-form
                v-if="loan"
                :item="loan"
                :selected-loanable-types="selectedLoanableTypes"
                @selectLoanableTypes="selectLoanableTypes"
                @selectLoanable="selectLoanable"
                :loading="searching || loading"
                :loanable-types="loanableTypes"
                :form="loanForm"
                :can-loan-car="canLoanCar"
                @changed="resetLoanables"
                @hide="searched = true"
                @submit="searchLoanables"
              />
            </div>
          </b-card>
          <!---->
        </b-col>

        <!-- Results header -->
        <b-col lg="8" xl="9">
          <!-- Small screens -->
          <b-card class="d-lg-none my-4">
            <div>
              <h3>Résultats</h3>
              <b-row align-v="center">
                <b-col sm="6" class="mb-3 mb-sm-0" v-if="searched">
                  <a class="community-view__button-modify-search" @click="searched = false"
                    >Modifier votre recherche</a
                  >
                </b-col>
              </b-row>
            </div>
          </b-card>
          <!---->

          <!-- Large Screens -->
          <div class="d-none d-lg-block">
              <div class="my-4">
                <!-- results header -->
                <h3>Résultats</h3>
                <!---->
              </div>
          </div>
          <!-- -->

          <!-- results display (loanable cards) -->
          <loanable-list
            v-if="view === 'list'"
            :data="loanables"
            :loading="searching || loading"
            @select="selectLoanable"
            @test="searchLoanables"
          />
          <!---->
        </b-col>
      </b-row>
    </div>
    <!---->
  </layout-page>
</template>
<script>
import Authenticated from "@/mixins/Authenticated";
import DataRouteGuards from "@/mixins/DataRouteGuards";
import UserMixin from "@/mixins/UserMixin";

import LoanableList from "@/components/Loanable/List.vue";
import LoanSearchForm from "@/components/Loan/SearchForm.vue";

import ListIcon from "@/assets/svg/list.svg";

import { buildComputed } from "@/helpers";

export default {
  name: "LoanableSearch",
  mixins: [Authenticated, DataRouteGuards, UserMixin],
  components: {
    LoanableList,
    LoanSearchForm,
    "svg-list": ListIcon,
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
          content:
            "Il faut avoir été accepté dans un quartier pour faire une recherche de véhicule",
          title: "En attente de validation",
          variant: "warning",
          type: "loan",
        });
        vm.$router.replace("/app", {});
      }

      next();
    });
  },
  mounted() {
    let selectedTypes = [];
    const possibleTypes = ["car"];
    const urlTypes = this.$route.query["types"];
    if (urlTypes) {
      selectedTypes = urlTypes
        .split(",")
        .filter((urlType) => !!possibleTypes.find((possibleType) => possibleType === urlType));
    } else {
      selectedTypes = this.selectedLoanableTypes;
    }

    if (!this.canLoanCar) {
      selectedTypes = selectedTypes.filter((t) => t !== "car");
    }

    this.updateSelectedTypes(selectedTypes, true);
  },
  computed: {
    ...buildComputed("loanable.search", [
      "center",
      "lastLoan",
      "searched",
      "selectedLoanableTypes",
    ]),
    loanables() {
      return this.$store.state.loanables.data;
    },
    loading() {
      return this.$store.state.loanables.loading;
    },
    isMap() {
      return this.view === "map" ? "community-list--no-pointer-events" : "";
    },
    isSearched() {
      return this.searched ? "community-view--searched" : "";
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
      this.$router.push(`/search/${view}`);
    },
    resetLoanables() {
      this.$store.dispatch(`loanables/reset`);
    },
    selectLoanableTypes(value) {
      this.updateSelectedTypes(value.filter((item, i, ar) => ar.indexOf(item) === i));
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
        car: loanable.type == "car" ? loanable : null,
        trailer: loanable.type == "trailer" ? loanable : null,
        bike: loanable.type == "bike" ? loanable : null,
        loanable_id: loanable.id,
        estimated_insurance: loanable.estimatedCost.insurance,
        estimated_price: loanable.estimatedCost.price,
        platform_tip: 0,
      });

      this.$router.push("/loans/new");
    },
    async searchLoanables() {
      this.searching = true;
      try {
        await this.$store.dispatch("loanables/search", {
          loan: this.loan,
        });
      } finally {
        this.searching = false;
      }
      this.searched = true;
    },
    updateSelectedTypes(selectedTypes) {
      this.selectedLoanableTypes = selectedTypes;
      const types = selectedTypes.join(",");

      if (this.$route.query.types !== types) {
        const query = {
          ...this.$route.query,
          types,
        };

        this.$router.replace({ ...this.$route, query });
      }

      this.reloading = true;
      this.$store.dispatch("loanables/list", { types });
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
  },
};
</script>

<style lang="scss">
@import "~bootstrap/scss/mixins/breakpoints";

.community-view {
  &__overlay {
    z-index: 20;
    position: relative;
  }

  &__button-modify-search {
    color: $primary !important;
    cursor: pointer;

    &:hover {
      color: $primary;
    }
  }

  &__button-container {
    pointer-events: all;
    position: absolute;
    right: 15px;
    top: 1.5rem;
  }

  &__button-spacing {
    display: flex;
    flex-direction: row;
  }

  &__button-spacing > div {
    display: flex;
    align-items: center;
    margin: 0 5px;
  }

  // Go to map & list buttons
  .btn-secondary {
    color: #7a7a7a;
    background: #fff;
    border: 1px solid #e5e5e5;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    box-sizing: border-box;
    margin: 0;

    &:hover {
      color: #7a7a7a;
      background: #fff;
      border: 1px solid #e5e5e5;
    }
  }

  .card {
    pointer-events: all;
  }

  &__search-container {
    margin-top: 1.5rem;
    overflow: auto;
  }

  h3 {
    font-weight: 700;

    @include media-breakpoint-down(md) {
      line-height: $h4-line-height;
      font-size: $h4-font-size;
    }
  }

  .community-map {
    height: 100vh;
    width: 100%;
    @include media-breakpoint-up(lg) {
      display: block;
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: calc(100vh - #{$layout-navbar-height});
      z-index: 10;
    }
  }
}

.community-view--map {
  .page__content {
    position: relative;
  }
  .community-view__search-container {
    @include media-breakpoint-up(lg) {
      max-height: calc(100vh - #{$layout-navbar-height} - 3rem);
    }
  }
}

.community-list--no-pointer-events {
  pointer-events: none;
}

@include media-breakpoint-down(md) {
  .community-view--searched {
    .community-view__search-container {
      display: none;
    }
  }
}

.community-view--margin-top {
  margin-top: 20px;
}
</style>
