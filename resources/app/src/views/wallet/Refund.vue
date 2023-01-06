<template>
  <b-container class="wallet-refund" fluid v-if="item && routeDataLoaded">
    <vue-headful :title="fullTitle" />

    <b-row>
      <b-col>
        <h1>
          {{ $t(item.id ? "modifier un" : "ajouter un") | capitalize }} {{ $tc("remboursement", 1) }}
        </h1>
      </b-col>
    </b-row>

    <b-row>
      <b-col>
        <b-form class="form" @submit.prevent="submit">
          <forms-builder :definition="form" v-model="item" entity="refunds" />

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

export default {
  name: "WalletRefund",
  mixins: [Authenticated, DataRouteGuards, FormMixin, UserMixin],
  components: {
    FormsBuilder
  },
  computed: {
    fullTitle() {
      const parts = [
        "LocoMotion",
        capitalize(this.$i18n.t("wallet.titles.wallet")),
        capitalize(this.$i18n.tc("remboursement", 2)),
      ];

      if (this.pageTitle) {
        parts.push(this.pageTitle);
      }

      return parts.reverse().join(" | ");
    },
    pageTitle() {
      return this.item.name || capitalize(this.$i18n.tc("remboursement", 1));
    },
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.refunds,
        ...locales.en.forms,
        titles: { ...locales.en.titles },
      },
      fr: {
        ...locales.fr.refunds,
        ...locales.fr.forms,
        titles: { ...locales.fr.titles },
      },
    },
  },
};
</script>
