<template>
  <b-card no-body class="loan-form loan-actions loan-actions-payment">
    <b-card-header header-tag="header" role="tab" class="loan-actions__header">
      <h2 v-b-toggle.loan-actions-payment>
        <svg-waiting v-if="action.status === 'in_process'" />
        <svg-check v-else-if="action.status === 'completed'" />
        <svg-danger v-else-if="action.status === 'canceled'" />

        Conclusion
      </h2>

      <span v-if="action.status == 'in_process'">En attente</span>
      <span v-else-if="action.status === 'completed'">
        Payé &bull; {{ action.executed_at | datetime }}
      </span>
      <span v-else-if="action.status === 'canceled'">
        Annulé &bull; {{ action.executed_at | datetime }}
      </span>
    </b-card-header>


    <b-card-body>
      <b-collapse id="loan-actions-payment" role="tabpanel" accordion="loan-actions"
        :visible="open">
        <div v-if="!!action.executed_at">
          <p>L'emprunt s'est conclu avec succès!</p>
        </div>
        <div v-else>
          <div v-if="userRole === 'owner'">
            <p>
              Validez dès maintenant les informations sur ce trajet:
              le kilomètrage, la facture d'essence&hellip;
            </p>

            <hr>

            <p>
              Vous recevrez {{ item.actual_price | currency }} pour l'emprunt.
            </p>
          </div>
          <div v-if="userRole === 'borrower'" class="text-center">
            <p>
              Validez dès maintenant les informations sur votre trajet:
              le kilomètrage, la facture d'essence&hellip;
            </p>

            <hr>

            <b-row>
              <b-col lg="6">
                <p>
                  Coût final du trajet:
                  <span v-b-popover.hover="priceTooltip">{{ finalPrice | currency }}</span>.
                </p>
              </b-col>

              <b-col lg="6">
                <div role="group" class="form-group">
                  <label for="platform_tip" class="d-block" id="__BVID__151__BV_label_">
                    {{ $t('fields.platform_tip') | capitalize }}
                    <b-badge pill variant="light"
                      v-b-popover.hover="$t('descriptions.platform_tip')">
                      ?
                    </b-badge>
                  </label>

                  <div class="bv-no-focus-ring">
                    <b-form-input
                      id="platform_tip" name="platform_tip"
                      type="number" :min="0" :step="0.01"
                      v-model="platformTip" />
                  </div>
                </div>
              </b-col>
            </b-row>

            <b-row v-if="!hasEnoughBalance">
              <b-col>
                <p>
                  Il manque de crédits à votre compte pour payer cet emprunt.<br>
                  <a href="#" v-b-modal.add-credit-modal>Ajoutez des crédits</a>
                </p>

                <b-modal id="add-credit-modal" title="Approvisionner mon compte" size="lg"
                  footer-class="d-none">
                  <user-add-credit-box :user="user" :minimum-required="finalPrice - user.balance"
                    @bought="reloadUserAndCloseModal" @cancel="closeModal"/>
                </b-modal>
              </b-col>
            </b-row>

            <div class="loan-actions-payment__buttons text-center">
              <b-button size="sm" variant="success" class="mr-3"
                :disabled="!hasEnoughBalance" @click="completeAction">
                Accepter
              </b-button>
            </div>

            <b-row class="loan-actions__alert">
              <b-col>
                <b-alert variant="warning" show>
                  Dans 48h, vous ne pourrez plus modifier vos informations.
                  Nous validerons le coût de l'emprunt avec les détails ci-dessus.
                </b-alert>
              </b-col>
            </b-row>
          </div>
        </div>
      </b-collapse>
    </b-card-body>
  </b-card>
</template>

<script>
import UserAddCreditBox from '@/components/User/AddCreditBox.vue';

import LoanActionsMixin from '@/mixins/LoanActionsMixin';

import { filters } from '@/helpers';
import locales from '@/locales';

const { currency } = filters;

export default {
  name: 'LoanActionsPayment',
  mixins: [LoanActionsMixin],
  mounted() {
    this.action.platform_tip = parseFloat(this.platformTip, 10);
  },
  components: {
    UserAddCreditBox,
  },
  data() {
    return {
      platformTip: this.item.platform_tip,
    };
  },
  computed: {
    finalPrice() {
      return this.item.actual_price
        + this.item.actual_insurance
        + parseFloat(this.platformTip, 10);
    },
    hasEnoughBalance() {
      return this.user.balance >= this.finalPrice;
    },
    priceTooltip() {
      const strParts = [];

      strParts.push(`Trajet: ${currency(this.item.actual_price)}`); // eslint-disable-line no-irregular-whitespace
      if (this.item.actual_insurance > 0) {
        strParts.push(`Assurance: ${currency(this.item.actual_insurance)}`); // eslint-disable-line no-irregular-whitespace
      }
      if (parseFloat(this.platformTip, 10) > 0) {
        strParts.push(`Contribution: ${currency(parseFloat(this.platformTip, 10))}`); // eslint-disable-line no-irregular-whitespace
      }

      return strParts.join(' \\ ');
    },
  },
  methods: {
    closeModal() {
      this.$bvModal.hide('add-credit-modal');
    },
    async reloadUserAndCloseModal() {
      await this.$store.dispatch('loadUser');
      this.closeModal();
    },
  },
  watch: {
    platformTip(val) {
      this.action.platform_tip = parseFloat(val, 10);
    },
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.loans,
        ...locales.en.forms,
      },
      fr: {
        ...locales.fr.loans,
        ...locales.fr.forms,
      },
    },
  },
};
</script>

<style lang="scss">
</style>
