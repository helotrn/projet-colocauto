<template>
  <b-container class="wallet-expense" fluid v-if="item && routeDataLoaded">
    <vue-headful :title="fullTitle" />

    <b-row>
      <b-col>
        <h1>
          {{ $t(item.id ? (item.locked ? "afficher une" : "modifier une") : "ajouter une") | capitalize }} {{ $tc("dépense", 1) }}
        </h1>
      </b-col>
    </b-row>

    <b-row v-if="item.changes && item.changes.length">
      <b-col class="mb-3">
        <h2>Historique des modifications</h2>
        <b-button block v-b-toggle.changes variant="outline bg-white border d-flex justify-content-between align-items-center text-left">
          {{item.changes.length}} modification{{item.changes.length > 1 ? 's' : ''}} effectuée{{item.changes.length > 1 ? 's' : ''}}
          <open-indicator />
        </b-button>
        <b-collapse id="changes" class="mb-4">
          <b-card>
            <p v-for="change in item.changes" :key="change.id" class="card-text border-bottom pb-3">
              <!-- split several lines descriptions -->
              <template v-for="description in change.description.split(',')">
                <!-- replace => with a SVG arrow -->
                <span>{{ description.split('=>')[0] }}</span>
                <template v-if="description.split('=>').length > 1">
                  <arrow-right />
                  <span>{{ description.split('=>')[1] }}</span>
                </template>
                <br/>
              </template>
              <small class="d-flex justify-content-between">
                <span>par {{ change.user.full_name }}</span>
                <span>le {{ new Date(change.created_at).toLocaleDateString() }}</span>
              </small>
            </p>
          </b-card>
        </b-collapse>
      </b-col>
    </b-row>

    <b-row>
      <b-col>
        <b-form class="form" @submit.prevent="submit">
          <forms-builder :definition="form" v-model="item" entity="expenses" :disabled="item.locked || !user.borrower.validated" />

          <div class="form__buttons">
            <b-button-group>
              <b-button variant="success" type="submit" :disabled="!changed || loading">
                Sauvegarder
              </b-button>
              <b-button v-if="!item.id" type="reset" :disabled="!changed" @click="reset"> Réinitialiser </b-button>
            </b-button-group>
          </div>
        </b-form>
      </b-col>
    </b-row>
  </b-container>
  <layout-loading v-else />
</template>

<script>
import FormsBuilder from "@/components/Forms/Builder.vue";

import Authenticated from "@/mixins/Authenticated";
import DataRouteGuards from "@/mixins/DataRouteGuards";
import FormMixin from "@/mixins/FormMixin";
import UserMixin from "@/mixins/UserMixin";

import locales from "@/locales";

import { capitalize } from "@/helpers/filters";
import OpenIndicator from "vue-select/src/components/OpenIndicator.vue";
import ArrowRight from "@/assets/svg/arrow-right.svg";

export default {
  name: "WalletExpense",
  mixins: [Authenticated, DataRouteGuards, FormMixin, UserMixin],
  components: {
    FormsBuilder,
    OpenIndicator,
    ArrowRight,
  },
  computed: {
    fullTitle() {
      const parts = [
        "Coloc'Auto",
        capitalize(this.$i18n.t("wallet.titles.wallet")),
        capitalize(this.$i18n.tc("dépense", 2)),
      ];

      if (this.pageTitle) {
        parts.push(this.pageTitle);
      }

      return parts.reverse().join(" | ");
    },
    pageTitle() {
      return this.item.name || capitalize(this.$i18n.tc("dépense", 1));
    },
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.expenses,
        ...locales.en.forms,
        titles: { ...locales.en.titles },
      },
      fr: {
        ...locales.fr.expenses,
        ...locales.fr.forms,
        titles: { ...locales.fr.titles },
      },
    },
  },
};
</script>

<style scoped>
  .btn > svg {
    fill: rgba(60, 60, 60, 0.5);
  }
  .not-collapsed > svg {
    transform: rotate(180deg);
  }
  .collapse .card-text:last-child {
    border-bottom: none!important;
    padding-bottom: 0!important;
  }
  .collapse .card {
    border-top-right-radius: 0;
    border-top-left-radius: 0;
  }
</style>
