<template>
  <div class="loan-form">
    <validation-observer ref="observer" v-slot="{ passes }" :xyz="sss">
      <b-form :novalidate="true" class="form loan-form__form"
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

        <div class="form__buttons">
          <b-button type="submit" variant="primary">Rechercher</b-button>

          <b-button type="reset" variant="outline-warning">Réinitialiser</b-button>
        </div>
      </b-form>
    </validation-observer>
  </div>
</template>

<script>
import FormsValidatedInput from '@/components/Forms/ValidatedInput.vue';

import locales from '@/locales';

export default {
  name: 'Form',
  components: {
    FormsValidatedInput,
  },
  props: {
    form: {
      type: Object,
      required: false,
      default() {
        return null;
      },
    },
    loanableTypes: {
      type: Array,
      required: false,
      default() {
        return [];
      },
    },
    loan: {
      type: Object,
      required: true,
    },
    selectedLoanableTypes: {
      type: Array,
      require: true,
    },
  },
  computed: {
    disabledDates() {
      return {
        to: this.$dayjs(this.loan.departure_at).subtract(1, 'day').toDate(),
      };
    },
    disabledDatesInThePast() {
      return {
        to: this.$dayjs().subtract(1, 'day').toDate(),
      };
    },
    disabledTimes() {
      const departure = this.$dayjs(this.loan.departure_at);
      if (departure.format('YYYY-MM-DD') < this.$dayjs(this.return_at).format('YYYY-MM-DD')) {
        return {};
      }

      const hours = [];
      const departureHour = this.$dayjs(this.loan.departure_at).hour();
      for (let i = departureHour - 1; i >= 0; i -= 1) {
        hours.push(i);
      }

      const minutes = [];
      if (this.$dayjs(this.returnAt).hour() === departureHour) {
        for (let i = this.$dayjs(this.loan.departure_at).minute(); i >= 0; i -= 1) {
          minutes.push(i);
        }
      }

      return {
        h: hours,
        m: minutes,
        s: [],
      };
    },
    disabledTimesInThePast() {
      const departure = this.$dayjs(this.loan.departure_at);

      if (departure.format('YYYY-MM-DD') > this.$dayjs().format('YYYY-MM-DD')) {
        return {};
      }

      const hours = [];
      const nowHour = this.$dayjs().hour();
      for (let i = nowHour - 1; i >= 0; i -= 1) {
        hours.push(i);
      }

      const minutes = [];
      if (departure.hour() === nowHour) {
        for (let i = this.$dayjs().minute(); i >= 0; i -= 1) {
          minutes.push(i);
        }
      }

      return {
        h: hours,
        m: minutes,
        s: [],
      };
    },
    returnAt: {
      get() {
        return this.$dayjs(this.loan.departure_at)
          .add(this.loan.duration_in_minutes, 'minute')
          .format('YYYY-MM-DD HH:mm:ss');
      },
      set(val) {
        this.loan.duration_in_minutes = this.$dayjs(val)
          .diff(this.$dayjs(this.loan.departure_at), 'minute');
      },
    },
  },
  methods: {
    emitLoanableTypes(value) {
      this.$emit('selectLoanableTypes', value);
    },
    label(key) {
      return this.$i18n.t(`fields.${key}`);
    },
    placeholderOrLabel(key) {
      if (this.$i18n.te(`placeholders.${key}`)) {
        return this.$i18n.t(`placeholders.${key}`);
      }

      return this.label(key);
    },
    submit() {
      this.$emit('submit');
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
