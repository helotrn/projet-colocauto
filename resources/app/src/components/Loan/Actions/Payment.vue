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
              Vous recevrez {{ loan.actual_price | currency }} pour l'emprunt.
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
                  Coût final du trajet: {{ finalPrice | currency }}.
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
                      type="number" :step="0.01"
                      v-model="platformTip" />
                  </div>
                </div>
              </b-col>
            </b-row>

            <div class="loan-actions-payment__buttons text-center">
              <b-button size="sm" variant="success" class="mr-3"
                @click="completeAction">
                Accepter
              </b-button>

              <b-button size="sm" variant="outline-danger" @click="cancelAction">
                Refuser
              </b-button>
            </div>
          </div>
        </div>
      </b-collapse>
    </b-card-body>
  </b-card>
</template>

<script>
import LoanActionsMixin from '@/mixins/LoanActionsMixin';

import locales from '@/locales';

export default {
  name: 'LoanActionsPrePayment',
  mixins: [LoanActionsMixin],
  data() {
    return {
      platformTip: this.loan.platform_tip,
    };
  },
  computed: {
    finalPrice() {
      return this.loan.actual_price
        + this.loan.actual_insurance
        + parseFloat(this.platformTip, 10);
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
