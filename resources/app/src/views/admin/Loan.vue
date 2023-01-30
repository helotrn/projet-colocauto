<template>
  <layout-loading v-if="!pageLoaded" />
  <div class="admin-loan-page" v-else>
    <vue-headful :title="fullTitle" />

    <div v-if="item.id">
      <loan-header :user="user" :loan="item" />

      <loan-action-buttons
        class="mb-3"
        :item="item"
        @extension="addExtension"
        @cancel="cancelLoan"
        @resume="resumeLoan"
        @incident="addIncident('accident')"
      />

      <b-row>
        <b-col lg="8">
          <div class="position-relative">
            <loan-actions
              :item="item"
              @load="loadItem"
              :form="form"
              :user="user"
              @submit="submit"
              :class="{ loading }"
            />
            <layout-loading v-if="loading" class="actions-loading-spinner"></layout-loading>
          </div>
        </b-col>
        <b-col lg="4" class="loan__sidebar" id="loan_details">
          <loan-details-box
            vertical
            :loan="item"
            :loanable="item.loanable"
            showInstructions
            :loan-loading="loading"
          />
        </b-col>
      </b-row>
    </div>
    <div v-else-if="form">
      <b-row>
        <b-col>
          <h1 v-if="item.name">{{ item.name }}</h1>
          <h1 v-else>
            <em>{{ $tc("model_name", 1) | capitalize }}</em>
          </h1>
        </b-col>
      </b-row>

      <b-row>
        <b-col>
          <validation-observer ref="observer" v-slot="{ passes, valid }">
            <b-form class="form" @submit.prevent="passes(submitAndReload)">
              <forms-builder :definition="form" v-model="item" entity="loans" />

              <div class="form__buttons">
                <b-button-group>
                  <b-button variant="success" type="submit" :disabled="!changed">
                    Sauvegarder
                  </b-button>
                  <b-button type="reset" :disabled="!changed" @click="reset">
                    Réinitialiser
                  </b-button>
                </b-button-group>
              </div>
            </b-form>
          </validation-observer>
        </b-col>
      </b-row>
    </div>
  </div>
</template>

<script>
import FormsBuilder from "@/components/Forms/Builder.vue";
import LoanActions from "@/components/Loan/Actions.vue";
import LoanActionButtons from "@/components/Loan/ActionButtons.vue";
import LoanDetailsBox from "@/components/Loan/DetailsBox.vue";
import LoanHeader from "@/components/Loan/LoanHeader.vue";

import Authenticated from "@/mixins/Authenticated";
import FormMixin from "@/mixins/FormMixin";
import LoanStepsSequence from "@/mixins/LoanStepsSequence";
import UserMixin from "@/mixins/UserMixin";

import locales from "@/locales";

import { capitalize } from "@/helpers/filters";

export default {
  name: "AdminLoan",
  mixins: [Authenticated, FormMixin, LoanStepsSequence, UserMixin],
  components: {
    FormsBuilder,
    LoanActions,
    LoanActionButtons,
    LoanHeader,
    LoanDetailsBox,
  },
  computed: {
    fullTitle() {
      const parts = [
        "Coloc'Auto",
        capitalize(this.$i18n.t("titles.admin")),
        capitalize(this.$i18n.tc("model_name", 2)),
      ];

      if (this.pageTitle) {
        parts.push(this.pageTitle);
      }

      return parts.reverse().join(" | ");
    },
    pageTitle() {
      return this.item.name || capitalize(this.$i18n.tc("model_name", 1));
    },
    pageLoaded() {
      // this.id is the route id
      return this.item && this.item.id == this.id && this.form;
    },
  },
  methods: {
    async resumeLoan() {
      this.$store.commit("loans/patchItem", {
        canceled_at: null,
      });
      await this.$store.dispatch("loans/updateItem", this.$route.meta.params);
    },
    async submitAndReload() {
      await this.submit();
    },
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.loans,
        ...locales.en.forms,
        titles: locales.en.titles,
      },
      fr: {
        ...locales.fr.loans,
        ...locales.fr.forms,
        titles: locales.fr.titles,
      },
    },
  },
};
</script>

<style lang="scss">
.admin-loan-page {
  .actions-loading-spinner {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
  }
  .loading {
    opacity: 0.5;
  }
}
</style>
