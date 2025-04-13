<template>
  <layout-page name="loan" :loading="!pageLoaded" padded>
    <div v-if="pageLoaded">
      <vue-headful :title="fullTitle" />
      <loan-header class="mb-5" :loan="item" :user="user"></loan-header>

      <b-row>
        <b-col lg="8" class="loan__actions">
          <b-row>
            <b-col md="3" class="d-lg-none">
              <div class="mb-3">
                <a href="#" v-scroll-to="'#loan_details'" to="#detail">Détails de l'emprunt</a>
              </div>
            </b-col>
          </b-row>

          <div class="position-relative">
            <loan-actions
              class="loan-actions"
              :class="{ loading }"
              :item="item"
              @load="loadItemAndUser"
              :form="loanForm"
              :user="user"
              @submit="submitLoan"
              @extension="addExtension"
            />
            <layout-loading v-if="loading" class="actions-loading-spinner"></layout-loading>
          </div>
        </b-col>

        <b-col lg="4" class="loan__sidebar" id="loan_details">
          <loan-details-box
            vertical
            :loan="item"
            :loanLoading="loading"
            :loanable="item.loanable"
            :showInstructions="hasReachedStep('intention') || userIsOwner"
          />
        </b-col>
      </b-row>
    </div>
  </layout-page>
</template>

<script>
import LoanActions from "@/components/Loan/Actions.vue";
import LoanActionButtons from "@/components/Loan/ActionButtons.vue";
import LoanHeader from "@/components/Loan/LoanHeader.vue";

import LoanDetailsBox from "@/components/Loan/DetailsBox.vue";

import Authenticated from "@/mixins/Authenticated";
import DataRouteGuards from "@/mixins/DataRouteGuards";
import FormMixin from "@/mixins/FormMixin";
import LoanStepsSequence from "@/mixins/LoanStepsSequence";
import UserMixin from "@/mixins/UserMixin";

import { capitalize } from "@/helpers/filters";

import locales from "@/locales";

export default {
  name: "Loan",
  mixins: [Authenticated, DataRouteGuards, FormMixin, LoanStepsSequence, UserMixin],
  components: {
    LoanActions,
    LoanActionButtons,
    LoanHeader,
    LoanDetailsBox,
  },
  beforeRouteEnter(to, from, next) {
    next((vm) => {
      if (vm.id === "new" && (!vm.item || !vm.item.loanable)) {
        vm.$router.replace("/search/map");
      }
    });
  },
  watch: {
    pageLoaded() {
      // make loan community the current one
      if(this.item?.loanable?.community?.id) {
        this.$store.dispatch('communities/setCurrent', {communityId: this.item.loanable.community.id})
      }
    }
  },
  computed: {
    pageLoaded() {
      // this.id is the route id
      return this.routeDataLoaded && this.item && (this.item.id == this.id || this.id === "new");
    },
    fullTitle() {
      const parts = ["Coloc'Auto", capitalize(this.$i18n.tc("titles.loan", 2))];

      if (this.pageTitle) {
        parts.push(this.pageTitle);
      }

      return parts.reverse().join(" | ");
    },
    loanForm() {
      return this.$store.state.loans.form;
    },
    pageTitle() {
      return this.item && this.item.loanable
        ? this.item.loanable.name
        : capitalize(this.$i18n.tc("titles.loanable", 1));
    },
  },
  methods: {
    async loadItemAndUser() {
      await Promise.all([this.loadItem(), this.$store.dispatch("loadUser")]);
    },
    skipLoadItem() {
      if (this.id === "new") {
        return true;
      }

      return false;
    },
    scrollToActiveAction() {
      // Only do it once
      var called = false;
      var self = this;
      return (function () {
        if (
          called ||
          self.id === "new" ||
          !self.item.id ||
          self.loanIsCanceled ||
          self.loanIsCompleted
        ) {
          return;
        }
        self.$scrollTo(`.loan-actions-${self.currentStep}`, 500, { offset: -20 });
        called = true;
      })();
    },
    async submitLoan() {
      await this.submit();
    },
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.loans,
      },
      fr: {
        ...locales.fr.loans,
      },
    },
  },
};
</script>

<style lang="scss">
.loan.page {
  .loan-actions.loading {
    opacity: 0.5;
    pointer-events: none;
  }
  .actions-loading-spinner {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
  }
}
</style>
