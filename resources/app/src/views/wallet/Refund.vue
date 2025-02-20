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
                <span>le {{ change.created_at | date }}</span>
              </small>
            </p>
          </b-card>
        </b-collapse>
      </b-col>
    </b-row>

    <b-row>
      <b-col>
        <b-form class="form" @submit.prevent="showConfirm = true">
          <forms-builder :definition="form" v-model="item" entity="refunds" :disabled="!user.borrower.validated" />

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
    <b-modal
      v-model="showConfirm"
      hide-header centered no-close-on-backdrop
      footer-class="justify-content-center mb-4"
      cancel-title="Annuler l’écriture du remboursement"
      cancel-variant="outline-primary"
      ok-title="Enregistrer le remboursement"
      @ok="submitRefund"
      @cancel="returnToRefundsList">
      <round-warning width="50px" class="mx-auto d-block my-4" />
      <p style="font-size:20px; text-align:center">
        Avant d’enregistrer le remboursement dans l’application, assurez-vous d’avoir bien remboursé la personne indiquée.
      </p>
    </b-modal>
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
import RoundWarning from "@/assets/icons/round-warning.svg";

export default {
  name: "WalletRefund",
  data: () => ({
    showConfirm: false,
    
  }),
  mixins: [Authenticated, DataRouteGuards, FormMixin, UserMixin],
  components: {
    FormsBuilder,
    OpenIndicator,
    ArrowRight,
    RoundWarning,
  },
  computed: {
    fullTitle() {
      const parts = [
        "Coloc'Auto",
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
  methods: {
    returnToRefundsList() {
      this.$router.push('/wallet/refunds');
    },
    afterSubmit() {
      // immediately show balance screen once refund has been added
      this.$router.push('/wallet/balance');
    },
    async submitRefund() {
      const isNew = !this.item.id;

      await this.submit();

      if (isNew) {
        this.$store.commit("addNotification", {
          content:
            "Le remboursement a bien été créé.",
          title: "Remboursement créé",
          variant: "success",
          type: "refund",
        });
      } else {
        this.$store.commit("addNotification", {
          content: "Le remboursement a été enregistré avec succès.",
          title: "Remboursement enregistré",
          variant: "success",
          type: "refund",
        });
      }
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
  watch: {
      item(){
        // get the amount from URL when needed
        if( this.item && !this.item.id) {
          if(this.item.amount == 0 && this.$route.query.amount) this.item.amount = this.$route.query.amount;
          if(!this.item.user_id && this.$route.query.user_id) this.item.user_id = this.$route.query.user_id;
          if(!this.item.credited_user_id && this.$route.query.credited_user_id) this.item.credited_user_id = this.$route.query.credited_user_id;
        }
        // make loanable community the current one
        if(this.item?.community?.id) {
          this.$store.dispatch('communities/setCurrent', {communityId: this.item.community.id})
        }
      }
  }
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
  ::v-deep .modal-content {
    background: #F5F8FB;
  }
  ::v-deep .modal-footer {
    border-top: none;
  }
</style>
