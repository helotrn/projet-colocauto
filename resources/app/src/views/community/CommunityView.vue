<template>
  <layout-page :name="`community-view community-view--${view}`" :loading="!routeDataLoaded" wide>
    <div :class="`community-view__overlay ${isMap}`">
      <b-row no-gutters>
        <b-col lg="3">
          <!-- loan search form container -->
          <b-card :class="isSearched">
            <div class="community-view__search-menu community-view--mobile-height">
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
                @reset="reset"
                @submit="searchLoanables"
              />
            </div>
          </b-card>
          <!---->
          <!-- results header (on mobile view) -->
          <b-card v-if="searched" class="community-view__results-container d-lg-none">
            <h3>Résultats de votre recherche</h3>
            <p>Prochaine étape: vérifier la disponibilité!</p>
            <div class="community-view--flex">
              <a class="community-view__button-modify-search" @click="searched = false"
                >Modifier votre recherche</a
              >
              <b-button v-if="view === 'map'" pill @click="gotoView('list')">
                <div class="community-view__button-spacing">
                  <div>Afficher la liste</div>
                  <div><svg-list /></div>
                </div>
              </b-button>
              <b-button v-else pill @click="gotoView('map')">
                <div class="community-view__button-spacing">
                  <div>Afficher la carte</div>
                  <div><svg-map /></div>
                </div>
              </b-button>
            </div>
          </b-card>
          <!---->
        </b-col>
        <b-col v-if="view === 'map'" lg="9">
          <!-- button to view list (on large screens) -->
          <div class="community-view__button-container d-none d-lg-block">
            <b-button pill @click="gotoView('list')">
              <div class="community-view__button-spacing">
                <div>Afficher la liste</div>
                <div><svg-list /></div>
              </div>
            </b-button>
          </div>
          <!---->
        </b-col>
        <b-col v-if="view === 'list' && searched" lg="9">
          <b-row no-gutters>
            <!-- results header (on large screens) -->
            <div
              class="community-view__results-container community-view--margin-top d-none d-lg-block"
            >
              <h3>Résultats de votre recherche</h3>
              <p>Prochaine étape: vérifier la disponibilité!</p>
            </div>
            <!---->
            <!-- button to view map (on large screens) -->
            <div class="community-view__button-container d-none d-lg-block">
              <b-button pill @click="gotoView('map')">
                <div class="community-view__button-spacing">
                  <div>Afficher la carte</div>
                  <div><svg-map /></div>
                </div>
              </b-button>
            </div>
            <!---->
          </b-row>
          <b-row no-gutters>
            <!-- results display (loanable cards) -->
            <community-list
              v-if="!loading"
              :data="data"
              :page="parseInt(params.page)"
              :per-page="parseInt(params.per_page)"
              :total="total ? total : 0"
              @page="setParam({ name: 'page', value: $event })"
              @select="selectLoanable"
              @test="searchLoanables"
            />
            <!---->
            <layout-loading class="col-lg-9" v-else />
          </b-row>
        </b-col>
      </b-row>
    </div>
    <!-- map display -->
    <community-map
      v-if="view === 'map'"
      :data="data"
      :communities="user.communities"
      @test="searchLoanables"
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
    if (!this.canLoanCar) {
      this.selectedLoanableTypes = this.selectedLoanableTypes.filter((t) => t !== "car");
    }
    this.resetPagination(this.view);
  },
  computed: {
    ...buildComputed("community.view", ["center", "lastLoan", "searched", "selectedLoanableTypes"]),
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
      this.setParam({ name: "page", value: "1" });
      switch (val) {
        case "list":
          this.setParam({ name: "per_page", value: "10" });
          break;
        case "map":
        default:
          this.setParam({ name: "per_page", value: "1000" });
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
    async searchLoanables() {
      this.searching = true;
      try {
        await this.$store.dispatch(`${this.slug}/search`, {
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
  &__overlay {
    z-index: 20;
    position: relative;
  }

  &__search-menu {
    max-height: calc(100vh - #{$layout-navbar-height} - 30px);
    overflow: auto;
    overflow-x: hidden;

    @include media-breakpoint-up(lg) {
      max-height: calc(100vh - #{$layout-navbar-height} - 70px);
      padding: 20px;
    }
  }

  &__results-container {
    width: 100%;
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
    right: 0;
    margin: 40px 20px;
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
    min-width: fit-content;

    &-body {
      padding: 0;

      @include media-breakpoint-down(md) {
        margin: 1.25rem;
      }
    }
    @include media-breakpoint-down(md) {
      border-radius: 0;
    }

    @include media-breakpoint-up(lg) {
      margin: 20px;
      max-height: 84vh;
    }
  }

  h3 {
    font-weight: 700;

    @include media-breakpoint-down(md) {
      line-height: $h4-line-height;
      font-size: $h4-font-size;
    }
  }

  .community-map {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: calc(100vh - #{$layout-navbar-height});
    z-index: 10;
  }
}

.community-view--map {
  .page__content {
    position: relative;
  }
}

.community-list--no-pointer-events {
  pointer-events: none;
}

.community-view--list {
  .page__content {
    padding: 0;
  }
}

.community-view--searched {
  @include media-breakpoint-down(md) {
    max-height: 0;
    overflow: hidden;

    + .card {
      max-height: 500px;
      background: $main-bg;
      border-radius: 0;
    }
  }
}

.community-view--flex {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 20px;
}

.community-view--margin-top {
  margin-top: 20px;
}

.community-view--mobile-height {
  @include media-breakpoint-down(md) {
    height: 100vh;
  }
}
</style>
