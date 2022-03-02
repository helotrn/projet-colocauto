<template>
  <b-card no-body class="loan-form loan-actions loan-actions-new">
    <b-card-header
      header-tag="header"
      role="tab"
      class="loan-actions__header"
      v-b-toggle.loan-actions-new
    >
      <h2>
        <svg-check v-if="item.id" />
        <svg-waiting v-else />

        Demande d'emprunt
      </h2>

      <span v-if="!item.created_at">En cours de création</span>
      <span v-else>Complété &bull; {{ item.created_at | datetime }}</span>
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
              <b-col lg="6">
                <!--
                  Disable past times only if loan is editable.
                -->
                <forms-validated-input
                  name="departure_at"
                  :disabled="!!item.id"
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
                  :disabled="!!item.id"
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

            <b-row v-if="invalid">
              <b-col>
                <div class="warning-message">
                  La durée de l'emprunt doit être supérieure ou égale à 15 minutes.
                </div>
              </b-col>
            </b-row>

            <b-row>
              <b-col lg="6">
                <b-row>
                  <b-col cols="6">
                    <div class="form-group">
                      <label>{{ $t("fields.duration_in_minutes") | capitalize }}</label>
                      <div v-if="!invalid">{{ item.duration_in_minutes }} minutes</div>
                    </div>
                  </b-col>

                  <b-col cols="6" v-if="item.loanable.type === 'car'">
                    <forms-validated-input
                      name="estimated_distance"
                      :label="$t('fields.estimated_distance') | capitalize"
                      type="text"
                      :min="10"
                      :max="1000"
                      :disabled="!!item.id"
                      :placeholder="placeholderOrLabel('estimated_distance') | capitalize"
                      v-model="item.estimated_distance"
                    />
                    <div class="warning-message" v-if="invalidDistance">
                      La distance prévue doit être un entier.
                    </div>
                  </b-col>
                </b-row>
              </b-col>

              <b-col lg="6" v-if="item.estimated_price > 0 || item.estimated_insurance > 0">
                <b-row>
                  <b-col cols="6">
                    <div class="form-group">
                      <label>{{ $t("fields.estimated_price") | capitalize }}</label>
                      <layout-loading
                        v-if="priceUpdating"
                        class="loan-form__estimations__loading"
                      />
                      <div v-else-if="!invalid">
                        {{ item.estimated_price | currency }}
                      </div>
                    </div>
                  </b-col>

                  <b-col cols="6">
                    <div class="form-group">
                      <label>{{ $t("fields.estimated_insurance") | capitalize }}</label>
                      <layout-loading
                        v-if="priceUpdating"
                        class="loan-form__estimations__loading"
                      />
                      <div v-else-if="!invalid">
                        {{ item.estimated_insurance | currency }}
                      </div>
                    </div>
                  </b-col>
                </b-row>
              </b-col>
            </b-row>

            <b-row>
              <b-col cols="6">
                <forms-validated-input
                  name="platform_tip"
                  :disabled="!!item.id"
                  :label="$t('fields.platform_tip') | capitalize"
                  :rules="{ required: true }"
                  type="currency"
                  :min="0"
                  :step="0.01"
                  :placeholder="placeholderOrLabel('platform_tip') | capitalize"
                  v-model="item.platform_tip"
                />
              </b-col>

              <b-col cols="6">
                <p>
                  LocoMotion est un projet citoyen et collaboratif. Les contributions volontaires
                  financent son fonctionnement.
                </p>
              </b-col>
            </b-row>

            <b-row>
              <b-col>
                <forms-validated-input
                  name="reason"
                  :disabled="!!item.id"
                  :label="$t('fields.reason') | capitalize"
                  :rules="form.reason.rules"
                  type="textarea"
                  :rows="3"
                  :placeholder="placeholderOrLabel('reason') | capitalize"
                  v-model="item.reason"
                />
              </b-col>

              <!-- No message for owner if the loanable is self service. -->
              <b-col xl="6" v-if="!loanableIsSelfService">
                <forms-validated-input
                  name="message_for_owner"
                  :disabled="!!item.id"
                  :label="
                    (`${$t('fields.message_for_owner')} ` + `(${$t('facultatif')})`) | capitalize
                  "
                  :rules="form.message_for_owner.rules"
                  type="textarea"
                  :rows="3"
                  :placeholder="placeholderOrLabel('message_for_owner') | capitalize"
                  v-model="item.message_for_owner"
                />
              </b-col>
            </b-row>

            <b-row class="form__buttons" v-if="!item.id">
              <b-col class="text-center">
                <b-button disabled type="submit" v-if="!item.loanable.available">
                  Indisponible
                </b-button>
                <b-button type="submit" :disabled="loading || isDisabled" v-else-if="isOwnedLoanable">
                  Faire la demande d'emprunt
                </b-button>
                <b-button type="submit" :disabled="loading" v-else>Réserver</b-button>
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

import FormLabelsMixin from "@/mixins/FormLabelsMixin";
import LoanFormMixin from "@/mixins/LoanFormMixin";
import LoanStepsSequence from "@/mixins/LoanStepsSequence";

import Check from "@/assets/svg/check.svg";
import Waiting from "@/assets/svg/waiting.svg";

import locales from "@/locales";

export default {
  name: "LoanForm",
  mixins: [FormLabelsMixin, LoanFormMixin, LoanStepsSequence],
  components: {
    FormsValidatedInput,
    "svg-check": Check,
    "svg-waiting": Waiting,
  },
  props: {
    open: {
      type: Boolean,
      required: false,
      default: false,
    },
    user: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      priceUpdating: false,
    };
  },
  methods: {
    submit() {
      this.$emit("submit");
    },
  },
  computed: {
    invalidDuration() {
      // Invalid if the duration is not greater than 0 minute.
      return !(this.item.duration_in_minutes > 0);
    },
    invalidDistance() {
      // Invalid if the estimated_distance value is not an int.
      let input = this.item.estimated_distance;
      return input.includes(".") || input.includes(",");
    },
    isDisabled() {
      // Search button is disabled if there is an invalid input.
      return this.invalidDuration || this.invalidDistance;
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
      });
    },
  },
  watch: {
    async loanParams(newValue, oldValue) {
      if (newValue !== oldValue) {
        this.priceUpdating = true;
        await this.$store.dispatch("loans/test", JSON.parse(newValue));
        this.priceUpdating = false;
      }
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
.loan-form__estimations__loading {
  max-width: 100px;
  max-height: 30px;
}
.loan-form {
  .warning-message {
    color: $danger;
    margin-bottom: 20px;
  }
}
</style>
