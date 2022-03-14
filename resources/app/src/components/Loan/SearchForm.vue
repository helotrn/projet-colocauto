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
          <h3 class="loan-search-form--no-margin">Qu'aimeriez-vous emprunter</h3>
          <h3 class="loan-search-form--green">à vos voisin-e-s?</h3>
        </div>
        <!---->
        <!-- buttons to select types of vehicles -->
        <b-form-group label-for="loanable_type">
          <b-form-checkbox-group
            switches
            stacked
            class="form__group"
            id="loanable_type"
            name="loanable_type"
            :options="loanableTypesExceptCar"
            :checked="selectedLoanableTypes"
            @change="emitLoanableTypes"
          >
            <template v-slot:first>
              <b-checkbox value="car" :disabled="!canLoanCar">
                Auto

                <b-badge
                  pill
                  variant="light"
                  v-if="!canLoanCar"
                  tabindex="0"
                  v-b-tooltip.hover
                  :title="
                    'Pour réserver une auto, remplissez le dossier de conduite ' +
                    'de votre profil.'
                  "
                >
                  ?
                </b-badge>
              </b-checkbox>
            </template>
          </b-form-checkbox-group>
        </b-form-group>
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
          <div
            v-if="invalidDuration"
            class="loan-search-form--warning loan-search-form--margin-bottom"
          >
            La durée de l'emprunt doit être supérieure ou égale à 15 minutes.
          </div>
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
            @click="$emit('hide')"
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

export default {
  name: "Form",
  components: { FormsValidatedInput },
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
  h3 {
    font-weight: 700;
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

.loan-search-form--warning {
  color: $danger;
}
</style>
