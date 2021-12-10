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

          <div v-if="userIsAdmin || userRoles.includes('borrower')">
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
          <div v-if="['borrower', 'owner'].some((role) => userRoles.includes(role))">
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

              <hr />

              <div v-if="!userIsAdmin">
                <b-row>
                  <b-col lg="3" />

                  <b-col lg="6">
                    <div role="group" class="form-group">
                      <label for="platform_tip" class="d-block" id="__BVID__151__BV_label_">
                        {{ $t("fields.platform_tip") | capitalize }}
                        <b-badge
                          pill
                          variant="light"
                          v-b-popover.hover="$t('descriptions.platform_tip')"
                        >
                          ?
                        </b-badge>
                      </label>

                      <div class="bv-no-focus-ring text-center">
                        <b-form-input
                          id="platform_tip"
                          name="platform_tip"
                          type="number"
                          :min="0"
                          :step="0.01"
                          v-model="platformTip"
                        />
                      </div>
                    </div>
                  </b-col>

                  <b-col lg="3" />
                </b-row>
              </div>

              <h3>Coût du trajet</h3>
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
                  <th v-if="userIsAdmin">
                    Montant remis à {{ item.loanable.owner.user.full_name }} par
                    {{ item.borrower.user.full_name }}&nbsp;:
                  </th>
                  <th v-else>Montant remis à {{ item.loanable.owner.user.full_name }}&nbsp;:</th>
                  <td class="trip-details__total text-right tabular-nums">
                    {{ actualOwnerPart | currency }}
                  </td>
                </tr>

                <tr>
                  <th>Assurances&nbsp;:</th>
                  <td class="text-right tabular-nums">
                    {{ item.actual_insurance | currency }}
                  </td>
                </tr>
                <tr>
                  <th>Contribution volontaire&nbsp;:</th>
                  <td class="text-right tabular-nums">
                    {{ parseFloat(this.platformTip, 10) | currency }}
                  </td>
                </tr>
                <tr>
                  <th>Total&nbsp;:</th>
                  <td class="trip-details__total text-right tabular-nums">
                    {{ actualPrice | currency }}
                  </td>
                </tr>
              </table>

              <b-row v-if="!userIsAdmin && !hasEnoughBalance">
                <b-col>
                  <br />
                  <p>
                    Il manque de crédits à votre compte pour payer cet emprunt.<br />
                    <a href="#" v-b-modal.add-credit-modal>Ajoutez des crédits</a>
                  </p>

                  <b-modal
                    id="add-credit-modal"
                    title="Approvisionner mon compte"
                    size="lg"
                    footer-class="d-none"
                  >
                    <user-add-credit-box
                      :user="user"
                      :minimum-required="actualPrice - user.balance"
                      @bought="reloadUserAndCloseModal"
                      @cancel="closeModal"
                    />
                  </b-modal>
                </b-col>
              </b-row>

              <div v-if="userIsAdmin">
                <div class="loan-actions-payment__buttons text-center">
                  <b-button
                    size="sm"
                    variant="success"
                    :disabled="actionLoading"
                    @click="completeAction"
                  >
                    Accepter
                  </b-button>
                </div>
              </div>

              <div v-else class="text-center">
                <div class="loan-actions-payment__buttons text-center">
                  <b-button
                    size="sm"
                    variant="success"
                    :disabled="!hasEnoughBalance || actionLoading"
                    @click="completeAction"
                  >
                    Accepter
                  </b-button>
                </div>
              </div>

              <b-row class="loan-actions__alert">
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
import UserAddCreditBox from "@/components/User/AddCreditBox.vue";

import LoanActionsMixin from "@/mixins/LoanActionsMixin";
import LoanStepsSequence from "@/mixins/LoanStepsSequence";

import { filters } from "@/helpers";
import locales from "@/locales";

const { currency } = filters;

export default {
  name: "LoanActionsPayment",
  mixins: [LoanActionsMixin, LoanStepsSequence],
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
    actualOwnerPart() {
      return this.item.actual_price - this.item.handover.purchases_amount;
    },
    finalOwnerPart() {
      return this.item.final_price - this.item.handover.purchases_amount;
    },
    actualPrice() {
      return (
        this.item.actual_price +
        this.item.actual_insurance +
        parseFloat(this.platformTip, 10) -
        this.item.handover.purchases_amount
      );
    },
    hasEnoughBalance() {
      return this.user.balance >= this.actualPrice;
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

      const purchasesAmount = parseFloat(this.item.handover.purchases_amount);
      if (purchasesAmount > 0) {
        strParts.push(`Achats: -${currency(purchasesAmount)}`); // eslint-disable-line no-irregular-whitespace
      }

      return strParts.join(" \\ ");
    },
  },
  methods: {
    closeModal() {
      this.$bvModal.hide("add-credit-modal");
    },
    async reloadUserAndCloseModal() {
      await this.$store.dispatch("loadUser");
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
.loan-actions-payment {
  .trip-details {
    margin: 0 auto;

    th,
    td {
      padding: 0 0.75rem;
    }
  }

  .trip-details__total {
    border-top: 1px solid black;
  }
}
</style>
