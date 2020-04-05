<template>
  <b-card no-body class="loan-form loan-actions loan-actions-new">
    <b-card-header header-tag="header" role="tab" class="loan-actions__header">
      <h2 v-b-toggle.loan-actions-new>
        <svg-check v-if="loan.id" />
        <svg-waiting v-else />

        Demande d'emprunt
      </h2>

      <span v-if="!loan.created_at">En cours de création</span>
      <span v-else>Complété &bull; {{ loan.created_at | datetime }}</span>
    </b-card-header>

    <b-card-body>
      <b-collapse id="loan-actions-new" role="tabpanel" accordion="loan-actions" :visible="open">
        <validation-observer ref="observer" v-slot="{ passes }">
          <b-form :novalidate="true" class="form loan-form__form"
            @submit.stop.prevent="passes(submit)"
            @reset.stop.prevent="$emit('reset')">
            <b-row>
              <b-col lg="6">
                <forms-validated-input name="departure_at"
                  :disabled="!!loan.id"
                  :label="$t('fields.departure_at') | capitalize"
                  :rules="form.departure_at.rules" type="datetime"
                  :disabled-dates="disabledDatesInThePast"
                  :disabled-times="disabledTimesInThePast"
                  :placeholder="placeholderOrLabel('departure_at') | capitalize"
                  v-model="loan.departure_at" />
              </b-col>

              <b-col lg="6">
                <forms-validated-input name="return_at"
                  :disabled="!!loan.id"
                  :label="$t('fields.return_at') | capitalize"
                  :rules="form.departure_at.rules" type="datetime"
                  :disabled-dates="disabledDates" :disabled-times="disabledTimes"
                  :placeholder="placeholderOrLabel('return_at') | capitalize"
                  v-model="returnAt" />
              </b-col>
            </b-row>

            <b-row>
              <b-col lg="3">
                <div class="form-group">
                  <label>{{ $t('fields.duration_in_minutes') | capitalize }}</label>
                  <div>
                    {{ loan.duration_in_minutes }} minutes
                  </div>
                </div>
              </b-col>

              <b-col lg="6">
                <forms-validated-input name="estimated_distance"
                  :label="$t('fields.estimated_distance') | capitalize"
                  type="number" :min="10" :max="1000"
                  :disabled="!!loan.id"
                  :placeholder="placeholderOrLabel('estimated_distance') | capitalize"
                  v-model="loan.estimated_distance" />
              </b-col>

              <b-col lg="3">
                <div class="form-group">
                  <label>{{ $t('fields.estimated_price') | capitalize }}</label>
                  <layout-loading v-if="priceUpdating" style="max-height: 30px;"/>
                  <div v-else-if="!loan.id">
                    <i v-b-tooltip.hover :title="loan.loanable.pricing">
                      {{ loan.estimated_price | currency }}
                    </i>
                  </div>
                  <div v-else>
                    {{ loan.estimated_price | currency }}
                  </div>
                </div>
              </b-col>
            </b-row>

            <b-row>
              <b-col xl="6">
                <forms-validated-input name="reason"
                  :disabled="!!loan.id"
                  :label="$t('fields.reason') | capitalize"
                  :rules="form.reason.rules" type="textarea" :rows="3"
                  :placeholder="placeholderOrLabel('reason') | capitalize"
                  v-model="loan.reason" />
              </b-col>

              <b-col xl="6">
                <forms-validated-input name="message_for_owner"
                  :disabled="!!loan.id"
                  :label="`${$t('fields.message_for_owner')} ` +
                    `(${$t('facultatif')})`| capitalize"
                  :rules="form.message_for_owner.rules" type="textarea" :rows="3"
                  :placeholder="placeholderOrLabel('message_for_owner') | capitalize"
                  v-model="loan.message_for_owner" />
              </b-col>
            </b-row>

            <b-row class="form__buttons" v-if="!loan.id">
              <b-col class="text-center">
                <b-button type="submit"
                  :disabled="priceUpdating || !loan.loanable.available">
                  {{ loan.loanable.available ? "Faire la demande d'emprunt" : 'Indisponible' }}
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
import FormsValidatedInput from '@/components/Forms/ValidatedInput.vue';

import FormLabelsMixin from '@/mixins/FormLabelsMixin';
import LoanFormMixin from '@/mixins/LoanFormMixin';

import Check from '@/assets/svg/check.svg';
import Waiting from '@/assets/svg/waiting.svg';

import locales from '@/locales';

export default {
  name: 'LoanForm',
  mixins: [FormLabelsMixin, LoanFormMixin],
  components: {
    FormsValidatedInput,
    'svg-check': Check,
    'svg-waiting': Waiting,
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
    };
  },
  methods: {
    submit() {
      this.$emit('submit');
    },
  },
  computed: {
    loanParams() {
      return JSON.stringify({
        departure_at: this.loan.departure_at,
        duration_in_minutes: this.loan.duration_in_minutes,
        estimated_distance: this.loan.estimated_distance,
        loanable_id: this.loan.loanable.id,
        community_id: this.loan.community_id,
      });
    },
  },
  watch: {
    async loanParams(newValue, oldValue) {
      if (newValue !== oldValue) {
        this.priceUpdating = true;
        await this.$store.dispatch('loans/test', JSON.parse(newValue));
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
</style>
