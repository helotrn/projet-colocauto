<template>
  <div class="loan-search-form">
    <validation-observer ref="observer" v-slot="{ passes }">
      <b-form :novalidate="true" class="form loan-search-form__form"
        @submit.stop.prevent="passes(submit)" @reset.stop.prevent="$emit('reset')">
        <div v-if="loan.departure_at">
          <forms-validated-input name="departure_at"
            :label="$t('fields.departure_at') | capitalize"
            :rules="form.departure_at.rules" type="datetime"
            :disabled-dates="disabledDatesInThePast" :disabled-times="disabledTimesInThePast"
            :placeholder="placeholderOrLabel('departure_at') | capitalize"
            v-model="loan.departure_at" />
        </div>

        <div v-if="loan.departure_at">
          <forms-validated-input name="return_at"
            :label="$t('fields.return_at') | capitalize"
            :rules="form.departure_at.rules" type="datetime"
            :disabled-dates="disabledDates" :disabled-times="disabledTimes"
            :placeholder="placeholderOrLabel('return_at') | capitalize"
            v-model="returnAt" />
        </div>

        <forms-validated-input name="loanable_type"
          :label="$t('fields.loanable_type') | capitalize"
          type="checkboxes" :options="loanableTypes"
          :placeholder="placeholderOrLabel('loanable_type') | capitalize"
          :value="selectedLoanableTypes"
          @input="emitLoanableTypes" />

        <forms-validated-input name="estimated_distance"
          :label="$t('fields.estimated_distance') | capitalize"
          type="number" :min="10" :max="1000"
          :placeholder="placeholderOrLabel('estimated_distance') | capitalize"
          v-model="loan.estimated_distance" />

        <div class="form__buttons mb-3">
          <b-button size="sm" type="submit" variant="primary" class="mr-3">
            Rechercher
          </b-button>

          <b-button size="sm" type="reset" variant="outline-warning">Réinitialiser</b-button>
        </div>
      </b-form>
    </validation-observer>
  </div>
</template>

<script>
import FormsValidatedInput from '@/components/Forms/ValidatedInput.vue';

import FormLabelsMixin from '@/mixins/FormLabelsMixin';
import LoanFormMixin from '@/mixins/LoanFormMixin';

import locales from '@/locales';

export default {
  name: 'Form',
  components: { FormsValidatedInput },
  mixins: [FormLabelsMixin, LoanFormMixin],
  props: {
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
      this.$emit('selectLoanableTypes', value);
    },
    submit() {
      this.$emit('submit');
    },
  },
  watch: {
    loan: {
      deep: true,
      handler() {
        this.$emit('changed');
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
</style>
