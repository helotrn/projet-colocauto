<template>
  <layout-page name="loan" :loading="!pageLoaded">
    <div v-if="pageLoaded">
      <vue-headful :title="fullTitle" />

      <loan-header :user="user" :loan="item" />

      <b-row>
        <b-col lg="3" class="loan__sidebar">
          <loan-menu :item="item" :user="user" />
        </b-col>

        <b-col lg="9" class="loan__actions">
          <loan-action-buttons
            :item="item"
            @extension="addExtension"
            @cancel="cancelLoan"
            @incident="addIncident('accident')"
          />

          <b-alert
            show
            variant="warning"
            v-if="
              !!item.id &&
              item.status === 'in_process' &&
              !loanIsCanceled &&
              !hasReachedStep('takeover') &&
              !hasReachedStep('handover') &&
              !hasReachedStep('payment')
            "
          >
            <h4>{{ $t("modification_warning.title") }}</h4>
            {{ $t("modification_warning.content") }}
          </b-alert>

          <b-alert
            show
            variant="info"
            v-if="item.loanable.type === 'car' && !loanIsCanceled && !hasReachedStep('handover')"
          >
            <h4 class="alert-heading">
              {{ $t("insurance_warning.title") }}
            </h4>

            <i18n path="insurance_warning.warning" tag="p">
              <template #link>
                <a :href="$t('insurance_warning.terms_link')" target="_blank">{{
                  $t("insurance_warning.terms")
                }}</a>
              </template>
              <template #emphasis>
                <strong>
                  {{ $t("insurance_warning.car_stays_in_quebec") }}
                </strong>
              </template>
            </i18n>
          </b-alert>

          <loan-actions
            :item="item"
            @load="loadItemAndUser"
            :form="loanForm"
            :user="user"
            @submit="submitLoan"
            @extension="addExtension"
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
import LoanMenu from "@/components/Loan/Menu.vue";

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
    LoanMenu,
  },
  beforeRouteEnter(to, from, next) {
    next((vm) => {
      if (vm.id === "new" && !vm.item.loanable) {
        vm.$router.replace("/community/list");
      }
    });
  },
  data() {
    return {
      loadedFullLoanable: false,
    };
  },
  computed: {
    fullTitle() {
      const parts = ["LocoMotion", capitalize(this.$i18n.tc("titles.loan", 2))];

      if (this.pageTitle) {
        parts.push(this.pageTitle);
      }

      return parts.reverse().join(" | ");
    },
    loanForm() {
      return this.$store.state.loans.form;
    },
    pageLoaded() {
      return this.routeDataLoaded && this.item && this.loadedFullLoanable;
    },
    pageTitle() {
      return this.item && this.item.loanable
        ? this.item.loanable.name
        : capitalize(this.$i18n.tc("titles.loanable", 1));
    },
  },
  methods: {
    async formMixinCallback() {
      const { id, type } = this.item.loanable;
      await this.$store.dispatch(`${type}s/retrieveOne`, {
        params: {
          fields:
            "*,owner.id,owner.user.id,owner.user.avatar,owner.user.name,owner.user.phone," +
            "community.name",
          "!fields": "events",
          with_deleted: true,
        },
        id,
      });
      const loanable = this.$store.state[`${type}s`].item;

      this.$store.commit(`${type}s/item`, null);

      this.$store.commit(`${this.slug}/mergeItem`, { loanable });

      this.loadedFullLoanable = true;
    },
    async loadItemAndUser() {
      this.loadedFullLoanable = false;

      await Promise.all([this.loadItem(), this.$store.dispatch("loadUser")]).then(() =>
        this.$store.dispatch("loadLoans")
      );
    },
    skipLoadItem() {
      if (this.id === "new") {
        return true;
      }

      return false;
    },
    async submitLoan() {
      await this.submit();
      await this.loadItemAndUser();
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
  .page__content {
    padding-top: 45px;
    padding-bottom: 45px;
  }

  .loan-menu {
    margin-bottom: 30px;
  }
}
</style>
