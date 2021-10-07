<template>
  <div class="loan-search-form">
    <validation-observer ref="observer" v-slot="{ passes }">
      <b-form
        :novalidate="true"
        class="form loan-search-form__form"
        @submit.stop.prevent="passes(submit)"
        @reset.stop.prevent="$emit('reset')"
      >
        <div v-if="form">
          <div v-if="item.departure_at">
            <forms-validated-input
              name="departure_at"
              :label="$t('fields.departure_at') | capitalize"
              :rules="form.departure_at.rules"
              type="datetime"
              :disabled-dates="disabledDatesInThePast"
              :disabled-times="disabledTimesInThePast"
              :placeholder="placeholderOrLabel('departure_at') | capitalize"
              v-model="item.departure_at"
            />
          </div>

          <div v-if="item.departure_at">
            <forms-validated-input
              name="return_at"
              :label="$t('fields.return_at') | capitalize"
              :rules="form.departure_at.rules"
              type="datetime"
              :disabled-dates="disabledDates"
              :disabled-times="disabledTimes"
              :placeholder="placeholderOrLabel('return_at') | capitalize"
              v-model="returnAt"
            />
          </div>

          <b-form-group :label="$t('fields.loanable_type') | capitalize" label-for="loanable_type">
            <b-form-checkbox-group
              stacked
              switches
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

          <forms-validated-input
            name="estimated_distance"
            :label="$t('fields.estimated_distance') | capitalize"
            type="number"
            :min="10"
            :max="1000"
            :placeholder="placeholderOrLabel('estimated_distance') | capitalize"
            v-model="item.estimated_distance"
          />

          <div class="form__buttons">
            <b-button
              size="sm"
              type="submit"
              variant="primary"
              class="mr-2 mb-2"
              :disabled="loading || invalid"
            >
              <b-spinner small v-if="loading" />
              Rechercher
            </b-button>

            <b-button
              size="sm"
              variant="info"
              class="ml-2 mb-2 d-block d-lg-none"
              @click="$emit('hide')"
            >
              Fermer
            </b-button>
          </div>
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
    invalid() {
      return this.item.duration_in_minutes < 15;
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
.loan-search-form {
  .form__buttons {
    display: flex;
    justify-content: space-between;
  }
}
</style>
