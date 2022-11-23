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
          <div class="loan-actions-payment__warning" v-if="loanIsContested">
            <p>
              Vous ne pouvez pas clôturer cette réservation car elle a été contestée, notre équipe
              va vous contacter.
            </p>
          </div>
          <div v-else>
            <!-- Validation header -->
            <div v-if="needsUserValidation">
              <p>
                Validez les informations sur ce trajet&nbsp;: le kilomètrage, la facture
                d'essence&hellip;
              </p>
              <p>
                <b-button size="sm" variant="primary" class="mr-3" v-b-toggle.loan-actions-takeover>
                  Informations au début
                </b-button>
                <b-button size="sm" variant="primary" v-b-toggle.loan-actions-handover>
                  Informations à la fin
                </b-button>
              </p>

              <hr />
            </div>
            <div v-else-if="needsValidation">
              <p v-if="needsBorrorwerValidation || needsOwnerValidation">
                En attente de la validation des informations par
                <span v-if="needsBorrorwerValidation">{{ item.borrower.user.full_name }}</span>
                <span v-if="needsBorrorwerValidation && needsOwnerValidation">et</span>
                <span v-if="needsOwnerValidation">{{ item.loanable.owner.user.full_name }}</span>
              </p>
              <p v-else>Emprunt entièrement validé. En attente du paiement!</p>

              <hr />
            </div>

            <!-- Owner content -->
            <div v-if="userRoles.includes('owner')">
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

              <div v-if="needsValidation" class="mt-4">
                <div v-if="!needsUserValidation" class="text-center">
                  <b-button variant="success" disabled> Validé! </b-button>
                </div>
                <div v-else-if="needsBorrorwerValidation">
                  <b-alert show variant="warning">
                    Le paiement sera effectué lorsque vous et l'emprunteur-se aurez validé les
                    informations, ou au plus tard 48h après la fin de la réservation. ({{
                      validationLimitText
                    }}).
                  </b-alert>
                  <div class="text-center">
                    <b-button variant="success" :disabled="updating" @click="validate">
                      Valider
                    </b-button>
                  </div>
                </div>
                <div v-else>
                  <b-alert show variant="success">
                    L'emprunteur-se a déjà validé l'information. Vous avez jusqu'à 48h après la fin
                    de la réservation ({{ validationLimitText }}) pour contester. Validez dès
                    maintenant pour recevoir le paiement!
                  </b-alert>
                  <div class="text-center">
                    <b-button
                      size="sm"
                      variant="success"
                      :disabled="updating"
                      @click="() => validate() && completeAction()"
                    >
                      Valider
                    </b-button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Borrower and admin content -->
            <div v-if="userIsAdmin || userRoles.includes('borrower')">
              <div v-if="!userIsAdmin">
                <b-row>
                  <b-col lg="6" offset-lg="3">
                    <div role="group" class="form-group">
                      <div class="bv-no-focus-ring text-center">
                        <forms-validated-input
                          id="platform_tip"
                          name="platform_tip"
                          type="currency"
                          :disabled="updating"
                          :label="$t('fields.platform_tip') | capitalize"
                          :description="$t('descriptions.platform_tip')"
                          :min="0"
                          :rules="{ min_value: 0 }"
                          v-model="platformTip"
                        />
                      </div>
                    </div>
                  </b-col>
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

              <div class="mt-3">
                <div v-if="userIsAdmin">
                  <div class="text-center">
                    <b-button
                      size="sm"
                      variant="warning"
                      :disabled="actionLoading"
                      @click="completeAction"
                    >
                      Compléter le paiement immédiatement
                    </b-button>
                  </div>
                </div>

                <!-- Can be paid if no validation needed or fully validated. -->
                <div
                  v-else-if="
                    !needsValidation || (!needsBorrorwerValidation && !needsOwnerValidation)
                  "
                >
                  <payment-dialog
                    :user="user"
                    :price="actualPrice"
                    @complete="() => updateTip() && completeAction()"
                    action-name="payer"
                  />
                </div>
                <div v-else-if="needsOwnerValidation && needsBorrorwerValidation">
                  <b-alert variant="warning" show v-if="needsOwnerValidation">
                    Le paiement sera effectué lorsque vous et le-a propriétaire aurez validé les
                    informations ou au plus tard 48h après la fin de la réservation. ({{
                      validationLimitText
                    }}).
                  </b-alert>

                  <payment-dialog
                    :user="user"
                    :price="actualPrice"
                    :loading="updating"
                    @complete="() => updateTip() && validate()"
                    action-name="valider"
                  />
                </div>
                <div v-else-if="needsBorrorwerValidation">
                  <b-alert variant="success" show>
                    Le-a propriétaire a déjà validé l'information. Vous avez jusqu'à 48h après la
                    fin de la réservation ({{ validationLimitText }}) pour pour contester. Sinon,
                    validez les informations et payez dès maintenant!
                  </b-alert>
                  <payment-dialog
                    :user="user"
                    :price="actualPrice"
                    @complete="() => updateTip() && validate() && completeAction()"
                    action-name="payer"
                    :loading="actionLoading || updating"
                  />
                </div>
                <div v-else-if="hasModifiedTip">
                  <b-alert variant="warning" show v-if="needsOwnerValidation">
                    <p>Enregistrez vos changements à la contribution volontaire.</p>

                    <p>
                      Le paiement sera effectué lorsque le-a propriétaire aura validé les
                      informations ou au plus tard 48h après la fin de la réservation ({{
                        validationLimitText
                      }}).
                    </p>
                  </b-alert>

                  <payment-dialog
                    :user="user"
                    :price="actualPrice"
                    @complete="updateTip"
                    action-name="Enregistrer"
                    :loading="updating"
                  />
                </div>
                <div v-else class="text-center">
                  <b-button disabled variant="success">Validé !</b-button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </b-collapse>
    </b-card-body>
  </b-card>
</template>

<script>
import FormsValidatedInput from "@/components/Forms/ValidatedInput.vue";
import PaymentDialog from "@/components/User/PaymentDialog.vue";

import LoanActionsMixin from "@/mixins/LoanActionsMixin";
import LoanStepsSequence from "@/mixins/LoanStepsSequence";

import { normalizeCurrency } from "@/helpers/filters";
import locales from "@/locales";

export default {
  name: "LoanActionsPayment",
  mixins: [LoanActionsMixin, LoanStepsSequence],
  components: {
    FormsValidatedInput,
    PaymentDialog,
  },
  data() {
    return {
      platformTip: normalizeCurrency(this.item.platform_tip),
      initialTip: normalizeCurrency(this.item.platform_tip),
      updating: false,
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
    needsValidation() {
      return this.item.needs_validation;
    },
    needsOwnerValidation() {
      return this.needsValidation && !this.item.owner_validated_at;
    },
    needsBorrorwerValidation() {
      // Or if user has modified tip!
      return this.needsValidation && !this.item.borrower_validated_at;
    },
    needsUserValidation() {
      return (
        (this.userIsBorrower && this.needsBorrorwerValidation) ||
        (this.userIsOwner && this.needsOwnerValidation)
      );
    },
    validationLimit() {
      return this.$dayjs(this.item.actual_return_at).add(48, "hour");
    },
    validationLimitText() {
      return this.validationLimit.format("D MMMM YYYY à HH:mm");
    },
    hasModifiedTip() {
      return this.normalizedTip !== this.initialTip;
    },
  },
  methods: {
    reloadUser() {
      this.$store.dispatch("loadUser");
    },
    // Returns true if tip has been updated successfully or if it didn't need to be updated.
    async updateTip() {
      if (this.hasModifiedTip) {
        try {
          this.updating = true;
          await this.$store.dispatch("loans/update", {
            id: this.item.id,
            data: { platform_tip: this.normalizedTip },
            params: this.$route.meta.params,
          });
          this.initialTip = this.normalizedTip;
          this.updating = false;
          return true;
        } catch (e) {
          return false;
        }
      }
      return true;
    },
    async validate() {
      try {
        this.updating = true;
        await this.$store.dispatch("loans/validate", { loan: this.item, user: this.user });
        return true;
      } catch (e) {
        return false;
      } finally {
        this.updating = false;
      }
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
