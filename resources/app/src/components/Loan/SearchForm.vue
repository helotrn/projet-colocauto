<template>
  <div class="loan-search-form">
    <validation-observer ref="observer" v-slot="{ passes }">
      <b-form
        :novalidate="true"
        class="form loan-search-form"
        @submit.stop.prevent="passes(submit)"
        @reset.stop.prevent="$emit('reset')"
      >
        <!-- title -->
        <div>
          <h4 class="loan-search-form--no-margin">Qu'aimeriez-vous emprunter</h4>
          <h4 class="loan-search-form--green">à vos voisin-e-s?</h4>
        </div>
        <!---->
        <!-- buttons to select types of vehicles -->
        <b-form-group>
          <b-form-checkbox-group
            buttons
            class="loanable-buttons"
            id="loanable_type"
            :checked="selectedLoanableTypes"
            @change="emitLoanableTypes"
          >
            <b-checkbox value="car" :disabled="!canLoanCar">
              <svg-car />
              Auto
            </b-checkbox>
            <b-checkbox value="bike">
              <svg-bike />
              Vélo
            </b-checkbox>
            <b-checkbox value="trailer">
              <svg-trailer />
              Remorque
            </b-checkbox>
          </b-form-checkbox-group>
        </b-form-group>
        <b-alert show variant="danger" v-if="!canLoanCar">
          <strong>Oops! Pour emprunter l'auto de vos voisin-e-s</strong> vous devez remplir votre
          dossier de conduite.
          <b-button
            to="/profile/borrower"
            pill
            class="loan-search-form__button-borrower"
            variant="danger"
          >
            Remplissez votre dossier
          </b-button>
        </b-alert>
        <!---->
        <div v-if="form">
          <div v-if="item.departure_at">
            <!-- field start time -->
            <forms-validated-input
              name="departure_at"
              :label="$t('fields.departure_at') | capitalize"
              :rules="form.departure_at.rules"
              type="datetime"
              :disabled-dates-fct="dateIsInThePast"
              :disabled-times-fct="timeIsInThePast"
              :placeholder="placeholderOrLabel('departure_at') | capitalize"
              v-model="item.departure_at"
            />
          </div>
          <!---->
          <!-- field end time -->
          <div v-if="item.departure_at">
            <forms-validated-input
              name="return_at"
              :label="$t('fields.return_at') | capitalize"
              :rules="form.departure_at.rules"
              type="datetime"
              :disabled-dates-fct="dateIsInThePast"
              :disabled-times-fct="timeIsInThePast"
              :placeholder="placeholderOrLabel('return_at') | capitalize"
              v-model="returnAt"
            />
          </div>
          <!---->
          <!-- text for loan invalid duration -->
          <b-alert show variant="danger" v-if="invalidDuration">
            La durée de l'emprunt doit être supérieure ou égale à 15 minutes.
          </b-alert>
          <!---->
          <!-- field estimated distance -->
          <div>
            <forms-validated-input
              name="estimated_distance"
              :label="$t('fields.estimated_distance') | capitalize"
              type="text"
              :min="10"
              :max="1000"
              :placeholder="placeholderOrLabel('estimated_distance') | capitalize"
              v-model="formattedEstimatedDistance"
            />
          </div>
          <!---->
          <!-- search button -->
          <b-button
            pill
            type="submit"
            variant="primary"
            class="mr-2 mb-2"
            :disabled="loading || invalidDuration"
          >
            <b-spinner small v-if="loading" />
            Rechercher
          </b-button>
          <!---->
        </div>
        <layout-loading v-else />
      </b-form>
    </validation-observer>
  </div>
</template>

<script>
import FormsValidatedInput from "@/components/Forms/ValidatedInput.vue";

import FormLabelsMixin from "@/mixins/FormLabelsMixin";
import LoanFormMixin from "@/mixins/LoanFormMixin";

import locales from "@/locales";

import Bike from "@/assets/svg/bike.svg";
import Car from "@/assets/svg/car.svg";
import Trailer from "@/assets/svg/trailer.svg";

export default {
  name: "Form",
  components: {
    FormsValidatedInput,
    "svg-bike": Bike,
    "svg-car": Car,
    "svg-trailer": Trailer,
  },
  mixins: [FormLabelsMixin, LoanFormMixin],
  props: {
    canLoanCar: {
      type: Boolean,
      required: false,
      default: false,
    },
    loading: {
      type: Boolean,
      required: false,
      default: false,
    },
    loanableTypes: {
      type: Array,
      required: false,
      default() {
        return [];
      },
    },
    selectedLoanableTypes: {
      type: Array,
      require: true,
    },
  },
  methods: {
    emitLoanableTypes(value) {
      this.$emit("selectLoanableTypes", value);
    },
    submit() {
      this.$emit("submit");
    },
  },
  computed: {
    invalidDuration() {
      // Invalid if the duration of a loan is not greater than 0 minute.
      return !(this.item.duration_in_minutes > 0);
    },
    loanableTypesExceptCar() {
      return this.loanableTypes.filter((t) => t.value !== "car");
    },
  },
  watch: {
    item: {
      deep: true,
      handler() {
        this.$emit("changed");
      },
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
@import "~bootstrap/scss/mixins/breakpoints";

.loan-search-form {
  &__button-borrower {
    margin: 15px 0 0 0;
  }

  h4 {
    font-size: 22px;
    font-weight: 700;
  }

  svg {
    height: 22px;
    width: 34px;
  }
  svg path {
    fill: currentColor;
  }

  .loanable-buttons label {
    border: 2px solid $locomotion-light-green;
    border-radius: 10px;
    display: table;
    font-size: 13px;
    line-height: 24px;
    width: 85px;
  }

  .loanable-buttons label:hover {
    background-color: #fff;
    border: 2px solid $locomotion-light-green;
  }

  .loanable-buttons > .btn:not(:last-child):not(.dropdown-toggle),
  .btn-group > .btn-group:not(:last-child) > .btn {
    border-radius: 10px;
    margin-right: 5px;
  }

  .loanable-buttons > .btn:not(:first-child),
  .btn-group > .btn-group:not(:first-child) > .btn {
    border-radius: 10px;
    margin-left: 5px;
  }

  .loanable-buttons .btn:not(:disabled):not(.disabled):active,
  .loanable-buttons .btn-secondary:not(:disabled):not(.disabled).active,
  .show > .loanable-buttons .btn-secondary.dropdown-toggle {
    background-color: $locomotion-light-green;
    border: 2px solid $locomotion-light-green;
    color: #fff;
  }

  .loanable-buttons .btn:focus,
  .btn.focus {
    background-color: #fff;
    color: #7a7a7a;
  }

  .loanable-buttons .btn:disabled,
  .btn.disabled {
    background-color: #fff;
    border-color: #a9afb5 !important;
    color: #7a7a7a;
  }
}

.loan-search-form--green {
  color: #00b1aa;
}

.loan-search-form--no-margin {
  margin: 0;
}

.loan-search-form--margin-bottom {
  margin-bottom: 15px;
}
</style>
