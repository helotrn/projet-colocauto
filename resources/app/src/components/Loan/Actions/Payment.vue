<template>
  <b-card no-body class="loan-form loan-actions loan-actions-payment">
    <b-card-header header-tag="header" role="tab" class="loan-actions__header">
      <h2 v-b-toggle.loan-actions-payment>
        <svg-waiting v-if="action.status === 'in_process' && !loanIsCanceled && !loanIsContested" />
        <svg-check v-else-if="action.status === 'completed'" />
        <svg-danger v-else-if="action.status === 'canceled' || loanIsCanceled || loanIsContested" />

        Conclusion
      </h2>

      <!-- Canceled loans: current step remains in-process. -->
      <span v-if="action.status === 'in_process' && loanIsCanceled">
        Emprunt annulé &bull; {{ item.canceled_at | datetime }}
      </span>
      <span v-else-if="action.status === 'in_process'"> En attente </span>
      <span v-else-if="action.status === 'completed'">
        Payé &bull; {{ action.executed_at | datetime }}
      </span>
      <span v-else-if="action.status === 'canceled'">
        Annulé &bull; {{ action.executed_at | datetime }}
      </span>
    </b-card-header>

    <b-card-body>
      <b-collapse
        id="loan-actions-payment"
        role="tabpanel"
        accordion="loan-actions"
        :visible="open"
      >
        <div v-if="!!action.executed_at">
          <p>L'emprunt s'est conclu avec succès!</p>

          <div v-if="(userIsAdmin || userRoles.includes('borrower')) && !item.is_free">
            <h3>Coût du trajet</h3>
            <table class="trip-details">
              <tr>
                <th>Temps et distance&nbsp;:</th>
                <td class="text-right tabular-nums">{{ item.final_price | currency }}</td>
              </tr>
              <tr>
                <th>Dépenses déduites&nbsp;:</th>
                <td class="text-right tabular-nums">
                  {{ -item.final_purchases_amount | currency }}
                </td>
              </tr>

              <tr>
                <th v-if="userIsAdmin">
                  Montant remis à {{ item.loanable.owner.user.full_name }} par
                  {{ item.borrower.user.full_name }}&nbsp;:
                </th>
                <th v-else>Montant remis à {{ item.loanable.owner.user.full_name }}&nbsp;:</th>
                <td class="trip-details__total text-right tabular-nums">
                  {{ finalOwnerPart | currency }}
                </td>
              </tr>

              <tr>
                <th>Assurances&nbsp;:</th>
                <td class="text-right tabular-nums">{{ item.final_insurance | currency }}</td>
              </tr>
              <tr>
                <th>Contribution volontaire&nbsp;:</th>
                <td class="text-right tabular-nums">
                  {{ item.final_platform_tip | currency }}
                </td>
              </tr>
              <tr>
                <th>Total&nbsp;:</th>
                <td class="trip-details__total text-right tabular-nums">
                  {{ item.total_final_cost | currency }}
                </td>
              </tr>
            </table>
          </div>

          <div v-if="userRoles.includes('owner')">
            <h3>Compensation du trajet</h3>
            <table class="trip-details">
              <tr>
                <th>Temps et distance&nbsp;:</th>
                <td class="text-right tabular-nums">{{ item.final_price | currency }}</td>
              </tr>
              <tr>
                <th>Dépenses déduites&nbsp;:</th>
                <td class="text-right tabular-nums">
                  {{ -item.final_purchases_amount | currency }}
                </td>
              </tr>
              <tr>
                <th>Total remis par {{ item.borrower.user.full_name }}&nbsp;:</th>
                <td class="trip-details__total text-right tabular-nums">
                  {{ finalOwnerPart | currency }}
                </td>
              </tr>
            </table>
          </div>
        </div>
        <div v-else-if="action.status === 'in_process' && loanIsCanceled">
          <p>L'emprunt a été annulé. Cette étape ne peut pas être complétée.</p>
        </div>
        <div v-else>
          <!-- Action is not completed -->
          <!-- Whether userRoles includes 'borrower' or 'owner' -->
          <div
            v-if="
              ['borrower', 'owner'].some((role) => userRoles.includes(role)) &&
              !item.loanable.is_self_service
            "
          >
            <p v-if="item.loanable.type === 'car'">
              Validez dès maintenant les informations sur ce trajet&nbsp;: le kilomètrage, la
              facture d'essence&hellip;
            </p>
            <p v-else>Validez dès maintenant les informations sur ce trajet.</p>
            <p>
              <b-button size="sm" variant="primary" class="mr-3" v-b-toggle.loan-actions-takeover>
                Informations au début
              </b-button>
              <b-button size="sm" variant="primary" v-b-toggle.loan-actions-handover>
                Informations à la fin
              </b-button>
            </p>
          </div>

          <div class="loan-actions-payment__warning" v-if="loanIsContested">
            <p>
              Vous ne pouvez pas clôturer cette réservation car elle a été contestée, notre équipe
              va vous contacter.
            </p>
          </div>

          <div v-else>
            <div v-if="userRoles.includes('owner')">
              <hr />

              <h3>Compensation du trajet</h3>
              <table class="trip-details">
                <tr>
                  <th>Temps et distance&nbsp;:</th>
                  <td class="text-right tabular-nums">{{ item.actual_price | currency }}</td>
                </tr>
                <tr>
                  <th>Dépenses déduites&nbsp;:</th>
                  <td class="text-right tabular-nums">
                    {{ -this.item.handover.purchases_amount | currency }}
                  </td>
                </tr>
                <tr>
                  <th>Total remis par {{ item.borrower.user.full_name }}&nbsp;:</th>
                  <td class="trip-details__total text-right tabular-nums">
                    {{ actualOwnerPart | currency }}
                  </td>
                </tr>
              </table>
            </div>

            <div v-if="userIsAdmin || userRoles.includes('borrower')">
              <div v-if="userIsAdmin">
                <p>L'emprunt est en phase de validation par les participant-e-s.</p>
              </div>

              <hr v-if="!item.loanable.is_self_service" />

              <div v-if="!userIsAdmin">
                <b-row>
                  <b-col lg="3" />

                  <b-col lg="6">
                    <div role="group" class="form-group">
                      <div class="bv-no-focus-ring text-center">
                        <forms-validated-input
                          id="platform_tip"
                          name="platform_tip"
                          type="currency"
                          :label="$t('fields.platform_tip') | capitalize"
                          :description="$t('descriptions.platform_tip')"
                          :min="0"
                          :rules="{ min_value: 0 }"
                          v-model="platformTip"
                        />
                      </div>
                    </div>
                  </b-col>

                  <b-col lg="3" />
                </b-row>
              </div>

              <template v-if="!item.is_free">
                <h3>Sommaire</h3>
                <table class="trip-details">
                  <tr class="header">
                    <th>Coût du trajet</th>
                    <td></td>
                  </tr>
                  <tr>
                    <th>Temps et distance</th>
                    <td class="text-right tabular-nums">{{ item.actual_price | currency }}</td>
                  </tr>

                  <tr>
                    <th>Dépenses déduites</th>
                    <td class="text-right tabular-nums">
                      {{ -this.item.handover.purchases_amount | currency }}
                    </td>
                  </tr>

                  <tr>
                    <th v-if="userIsAdmin">
                      Montant remis à {{ item.loanable.owner.user.full_name }} par
                      {{ item.borrower.user.full_name }}
                    </th>
                    <th v-else>Montant remis à {{ item.loanable.owner.user.full_name }}</th>
                    <td class="trip-details__total text-right tabular-nums">
                      {{ actualOwnerPart | currency }}
                    </td>
                  </tr>

                  <tr>
                    <th>Assurances</th>
                    <td class="text-right tabular-nums">
                      {{ item.actual_insurance | currency }}
                    </td>
                  </tr>
                  <tr>
                    <th>Contribution volontaire</th>
                    <td class="text-right tabular-nums">
                      {{ this.normalizedTip | currency }}
                    </td>
                  </tr>
                  <tr class="last">
                    <th>Total</th>
                    <td class="trip-details__total text-right tabular-nums">
                      <strong>{{ actualPrice | currency }}</strong>
                    </td>
                  </tr>
                  <template v-if="!userIsAdmin">
                    <tr class="header">
                      <th>Paiement</th>
                      <td></td>
                    </tr>
                    <tr>
                      <th>Solde actuel</th>
                      <td class="text-right tabular-nums">
                        {{ user.balance | currency }}
                      </td>
                    </tr>
                    <tr v-if="actualPrice > user.balance" class="last">
                      <th>Minimum à ajouter</th>
                      <td class="trip-details__total text-right tabular-nums">
                        <strong>{{ (actualPrice - user.balance) | currency }}</strong>
                      </td>
                    </tr>
                    <tr v-else class="last">
                      <th>Solde après paiement</th>
                      <td class="trip-details__total text-right tabular-nums">
                        <strong>{{ (user.balance - actualPrice) | currency }}</strong>
                      </td>
                    </tr>
                  </template>
                </table>
              </template>

              <b-row v-if="!userIsAdmin && !hasEnoughBalance">
                <b-col>
                  <br />
                  <p>Il manque de crédits à votre compte pour payer cet emprunt.</p>
                  <user-add-credit-box
                    :payment-methods="user.payment_methods"
                    :minimum-required="actualPrice - user.balance"
                    :trip-cost="actualPrice"
                    :no-cancel="true"
                    @bought="completePayment"
                  />
                </b-col>
              </b-row>

              <div v-if="userIsAdmin">
                <div class="loan-actions-payment__buttons text-center">
                  <b-button
                    size="sm"
                    variant="success"
                    :disabled="actionLoading"
                    @click="completePayment"
                  >
                    Accepter
                  </b-button>
                </div>
              </div>
              <div v-else-if="hasEnoughBalance" class="text-center">
                <div class="loan-actions-payment__buttons text-center">
                  <b-button
                    size="sm"
                    variant="success"
                    :disabled="actionLoading"
                    @click="completePayment"
                  >
                    Accepter
                  </b-button>
                </div>
              </div>

              <b-row v-if="!item.loanable.is_self_service" class="loan-actions__alert">
                <b-col>
                  <b-alert variant="warning" show>
                    Les informations de l'emprunt peuvent être modifiées jusqu'à 48h après sa
                    conclusion. À partir de ce moment, le coût de l'emprunt sera validé avec les
                    détails ci-dessus.
                  </b-alert>
                </b-col>
              </b-row>
            </div>
          </div>
        </div>
      </b-collapse>
    </b-card-body>
  </b-card>
</template>

<script>
import FormsValidatedInput from "@/components/Forms/ValidatedInput.vue";
import UserAddCreditBox from "@/components/User/AddCreditBox.vue";

import LoanActionsMixin from "@/mixins/LoanActionsMixin";
import LoanStepsSequence from "@/mixins/LoanStepsSequence";

import { normalizeCurrency } from "@/helpers/filters";
import locales from "@/locales";

export default {
  name: "LoanActionsPayment",
  mixins: [LoanActionsMixin, LoanStepsSequence],
  components: {
    FormsValidatedInput,
    UserAddCreditBox,
  },
  data() {
    return {
      platformTip: normalizeCurrency(this.item.platform_tip),
    };
  },
  computed: {
    normalizedTip() {
      return normalizeCurrency(this.platformTip);
    },
    purchasesAmount() {
      return normalizeCurrency(this.item.handover.purchases_amount);
    },
    actualOwnerPart() {
      return this.item.actual_price - this.purchasesAmount;
    },
    finalOwnerPart() {
      return this.item.final_price - this.purchasesAmount;
    },
    actualPrice() {
      return normalizeCurrency(
        this.item.actual_price +
          this.item.actual_insurance +
          this.normalizedTip -
          this.purchasesAmount
      );
    },
    hasEnoughBalance() {
      return this.user.balance >= this.actualPrice;
    },
  },
  methods: {
    completePayment() {
      this.action.platform_tip = this.normalizedTip;
      this.completeAction();
    },
  },
  watch: {
    normalizedTip(val) {
      this.action.platform_tip = val;
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
.loan-actions-payment {
  .trip-details {
    margin: 0 auto;
    line-height: 1.5;

    th,
    td {
      padding: 0 0.75rem;
      font-weight: normal;
    }

    tr:not(.header):not(.last) th,
    tr:not(.header):not(.last) td {
      border-bottom: 1px dotted lightgray;
    }

    .header th {
      font-weight: bold;
      font-size: 1.2rem;
      padding-top: 0.5rem;
    }
  }

  .trip-details__total {
    border-top: 1px solid black;
  }
}
</style>
