<template>
  <b-card no-body class="loan-form loan-actions loan-actions-new">
    <b-card-header
      header-tag="header"
      role="tab"
      class="loan-actions__header"
      v-b-toggle.loan-actions-new
    >
      <b-icon icon="chevron-down" class="toggle-icon"></b-icon>
      <h2>
        <svg-check v-if="item.id" />
        <svg-waiting v-else />
        Réservation
        <small v-if="item.borrower && item.borrower.user">par {{ item.borrower.user.full_name }}</small>
      </h2>

      <span v-if="!item.created_at">En cours de création</span>
      <span v-else>Complété &bull; {{ item.created_at | datetime }}</span>
      <b-row class="show-if-collapsed">
        <b-col tag="dl" md="3" cols="6">
          <dt>{{ $t("fields.departure_at") | capitalize }}</dt>
          <dd>
            {{ item.departure_at | dateWithWeekDay | capitalize }}<br />{{ item.departure_at | time }}
          </dd>
        </b-col>
        <b-col tag="dl" md="3" cols="6">
          <dt>{{ $t("fields.return_at") | capitalize }}</dt>
          <dd>
            {{ formatReturnAt | dateWithWeekDay | capitalize }}<br />
            {{ formatReturnAt | time }}
          </dd>
        </b-col>
        <b-col tag="dl" md="3" cols="6" v-if="price > 0 && distance > 0">
          <dt>
            {{
              hasFinalDistance
                ? $t("details_box.distance")
                : $t("details_box.estimated_distance") | capitalize
            }}
          </dt>
          <dd>{{ distance }} km</dd>
        </b-col>
        <b-col tag="dl" md="3" cols="6" v-if="price > 0 && distance > 0">
          <dt>
            {{
              item.final_price
                ? $t("details_box.cost")
                : $t("fields.estimated_price") | capitalize
            }}
          </dt>
          <dd>{{ totalPrice | currency }}</dd>
        </b-col>
      </b-row>
    </b-card-header>

    <b-card-body>
      <b-collapse id="loan-actions-new" role="tabpanel" accordion="loan-actions" :visible="open">
        <validation-observer ref="observer" v-slot="{ passes }">
          <b-form
            :novalidate="true"
            class="form loan-form__form"
            @submit.stop.prevent="passes(submit)"
            @reset.stop.prevent="$emit('reset')"
          >
            <b-row>
              <b-col>
                <b-alert show variant="warning" v-if="item.loanable.comments">
                  <div class="alert-heading">
                    <h4>Commentaires du propriétaire sur le véhicule</h4>
                  </div>
                  <div class="owner-comments-text">
                    <p>{{ item.loanable.comments }}</p>
                  </div>
                </b-alert>
              </b-col>
            </b-row>

            <b-row>
              <b-col lg="6">
                <!--
                  Disable past times only if loan is editable.
                -->
                <forms-validated-input
                  name="departure_at"
                  :disabled="!canEdit"
                  :label="$t('fields.departure_at') | capitalize"
                  :rules="form.departure_at.rules"
                  type="datetime"
                  :disabled-dates-fct="!item.id ? dateIsInThePast : null"
                  :disabled-times-fct="!item.id ? timeIsInThePast : null"
                  :placeholder="placeholderOrLabel('departure_at') | capitalize"
                  v-model="item.departure_at"
                />
              </b-col>

              <b-col lg="6">
                <!--
                  Disable past times only if loan is editable.
                -->
                <forms-validated-input
                  name="return_at"
                  :disabled="!canEdit"
                  :label="$t('fields.return_at') | capitalize"
                  :rules="form.departure_at.rules"
                  type="datetime"
                  :disabled-dates-fct="!item.id ? dateIsInThePast : null"
                  :disabled-times-fct="!item.id ? timeIsInThePast : null"
                  :placeholder="placeholderOrLabel('return_at') | capitalize"
                  v-model="returnAt"
                />
              </b-col>
            </b-row>

            <b-row v-if="invalidDuration">
              <b-col>
                <b-alert show variant="danger">
                  La durée de l'emprunt doit être supérieure ou égale à 30 minutes.
                </b-alert>
              </b-col>
            </b-row>

            <b-row>
              <b-col>
                <forms-validated-input
                  name="reason"
                  :disabled="!canEdit"
                  :label="$t('fields.reason') | capitalize"
                  :rules="form.reason.rules"
                  type="text"
                  :placeholder="placeholderOrLabel('reason') | capitalize"
                  :description="$t('descriptions.reason') | capitalize"
                  v-model="item.reason"
                />
              </b-col>
            </b-row>

            <b-row v-if="item.loanable.type === 'car'">
              <b-col md="6" >
                <forms-validated-input
                  name="estimated_distance"
                  :label="$t('fields.estimated_distance') | capitalize"
                  type="text"
                  :min="10"
                  :max="1000"
                  :disabled="!canEdit"
                  :placeholder="placeholderOrLabel('estimated_distance') | capitalize"
                  :description="$t('descriptions.estimated_distance') | capitalize"
                  v-model="formattedEstimatedDistance"
                />
              </b-col>
              <b-col md="6" >
                <loan-price-details :loan="item" :loan-loading="loading" />
              </b-col>
            </b-row>

            <b-row class="form__buttons" v-if="canEdit">
              <b-col md="6">
                <b-button
                  v-if="item.id"
                  @click="cancelLoan"
                  :disabled="loanIsCanceled || !isBorrower"
                  class="mb-2 py-2 w-100 leading-6"
                  variant="outline-danger"
                >
                  Annuler la réservation
                </b-button>
              </b-col>
              <b-col md="6">
                <b-button
                  type="submit"
                  :disabled="item.id && (!changed || !item.loanable.available || priceUpdating || invalidDuration)"
                  class="mb-2 py-2 w-100 leading-6"
                  variant="primary"
                >
                  {{ changed && !priceUpdating && !item.loanable.available ? 'Indisponible' : 'Enregistrer les modifications' }}
                </b-button>
              </b-col>
            </b-row>
          </b-form>
        </validation-observer>
      </b-collapse>
    </b-card-body>
  </b-card>
</template>

<script>
import FormsValidatedInput from "@/components/Forms/ValidatedInput.vue";
import LoanPriceDetails from "@/components/Loan/PriceDetails";

import FormLabelsMixin from "@/mixins/FormLabelsMixin";
import LoanFormMixin from "@/mixins/LoanFormMixin";
import UserMixin from "@/mixins/UserMixin";
import LoanStepsSequence from "@/mixins/LoanStepsSequence";

import Check from "@/assets/svg/check.svg";
import Waiting from "@/assets/svg/waiting.svg";

import locales from "@/locales";

export default {
  name: "LoanForm",
  mixins: [FormLabelsMixin, LoanFormMixin, LoanStepsSequence, UserMixin],
  components: {
    FormsValidatedInput,
    LoanPriceDetails,
    "svg-check": Check,
    "svg-waiting": Waiting,
  },
  props: {
    open: {
      type: Boolean,
      required: false,
      default: false,
    },
  },
  data() {
    return {
      priceUpdating: false,
      changed: false,
      throttle: new Date,
      debounce: null,
    };
  },
  methods: {
    submit() {
      this.$emit("submit");
      this.changed = false;
    },
  },
  computed: {
    invalidDuration() {
      // Invalid if the duration is not greater than 30 minute.
      return !(this.item.duration_in_minutes >= 30);
    },
    loading() {
      return this.$store.state.loans.loading;
    },
    loanParams() {
      return JSON.stringify({
        departure_at: this.item.departure_at,
        duration_in_minutes: this.item.duration_in_minutes,
        estimated_distance: this.item.estimated_distance,
        loanable_id: this.item.loanable.id,
        community_id: this.item.community_id,
      });
    },
    canEdit() {
      // Can edit if:
      return (
        !this.item.id ||
        this.isAdmin ||
        // or the loanable has not yet been taken
        (!this.hasReachedStep("takeover") && !this.loanIsCanceled) ||
        // or the reservation has not yet started
        this.$second.isBefore(this.item.departure_at, "minute")
      );
    },
    formatReturnAt() {
      if (this.item.actual_return_at) {
        return this.item.actual_return_at;
      }
      return this.$dayjs(this.item.departure_at)
        .add(this.item.duration_in_minutes, "minute")
        .format("YYYY-MM-DD HH:mm:ss");
    },
    hasFinalDistance() {
      return this.item.handover && this.item.handover.mileage_end;
    },
    distance() {
      if (this.hasFinalDistance) {
        return this.item.handover.mileage_end - this.item.takeover.mileage_beginning;
      }
      return this.item.estimated_distance;
    },
    price() {
      return this.item.final_price
        ? parseFloat(this.item.final_price)
        : this.item.actual_price
        ? parseFloat(this.item.actual_price)
        : parseFloat(this.item.estimated_price);
    },
  },
  watch: {
    async loanParams(newValue, oldValue) {
      // avoid calling the test function too early or too often
      if (new Date - this.throttle > 800 && newValue !== oldValue) {
        if( this.debounce ) clearTimeout(this.debounce);
        this.debounce = setTimeout(async () => {
          this.priceUpdating = true;
          this.changed = true;
          await this.$store.dispatch("loans/test", { ...JSON.parse(newValue), loan_to_change: this.item.id});
          this.priceUpdating = false;
        }, 200);
      }
    }
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
.loan-form__estimations__loading {
  max-width: 100px;
  max-height: 30px;
}
.owner-comments-text {
  white-space: pre-wrap;
}
.loan-actions__header {
  dt, dd {
    line-height: 1.2;
  }
  &.not-collapsed .show-if-collapsed {
    display: none;
  }
}
</style>
