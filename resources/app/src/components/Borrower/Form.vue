<template>
  <div class="borrower-form">
    <div class="borrower-form__text">

    </div>

    <validation-observer ref="observer" v-slot="{ passes }">
      <b-form :novalidate="true" class="borrower-form__form"
        @submit.stop.prevent="passes(submit)">
        <b-row>
          <b-col>
            <forms-validated-input name="drivers_license_number" :label="$t('fields.drivers_license_number') | capitalize" type="text"
              :placeholder="placeholderOrLabel('drivers_license_number') | capitalize"
              v-model="borrower.drivers_license_number" />
          </b-col>
        </b-row>

        <b-row>
          <b-col>
            <forms-validated-input name="has_been_sued_last_ten_years" type="checkbox"
              :label="$t('fields.has_been_sued_last_ten_years') | capitalize"
              :placeholder="placeholderOrLabel('has_been_sued_last_ten_years') | capitalize"
              v-model="borrower.has_been_sued_last_ten_years" />
          </b-col>
        </b-row>

        <b-row>
          <b-col>
            <forms-file-uploader field="saaq"
              :label="$t('fields.saaq') | capitalize"
              v-model="borrower.saaq" />
          </b-col>
        </b-row>

        <b-row>
          <b-col>
            <forms-file-uploader field="gaa"
              :label="$t('fields.gaa') | capitalize"
              v-model="borrower.gaa" />
          </b-col>
        </b-row>

        <b-row>
          <b-col>
            <div class="borrower-form__insurance">
              <div class="borrower-form__insurance__text">
                <p>Les documents d'assurances</p>
              </div>

              <forms-file-uploader field="insurance"
                :label="$t('fields.insurance') | capitalize"
                v-model="borrower.insurance" />
            </div>
          </b-col>
        </b-row>

        <div class="form__buttons" v-if="showButton">
          <b-button variant="success" type="submit" :disabled="loading">
            {{ $t('enregistrer') | capitalize }}
          </b-button>
        </div>
      </b-form>
    </validation-observer>
  </div>
</template>

<script>
import FormsValidatedInput from '@/components/Forms/ValidatedInput.vue';
import FormsFileUploader from '@/components/Forms/FileUploader.vue';

import locales from '@/locales';

export default {
  name: 'BorrowerForm',
  components: {
    FormsFileUploader,
    FormsValidatedInput,
  },
  props: {
    changed: {
      type: Boolean,
      required: false,
      default: false,
    },
    loading: {
      type: Boolean,
      required: true,
    },
    showButton: {
      type: Boolean,
      required: false,
    },
    borrower: {
      type: Object,
      required: true,
    },
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.borrowers,
        ...locales.en.forms,
      },
      fr: {
        ...locales.fr.borrowers,
        ...locales.fr.forms,
      },
    },
  },
  methods: {
    placeholderOrLabel(key) {
      if (this.$i18n.te(`placeholders.${key}`)) {
        return this.$i18n.t(`placeholders.${key}`);
      }

      return this.label(key);
    },
    label(key) {
      return this.$i18n.t(`fields.${key}`);
    },
    getValidationState({ dirty, validated, valid = null }) {
      return dirty || validated ? valid : null;
    },
    submit(...params) {
      this.$emit('submit', ...params);
    },
  },
};
</script>

<style lang="scss">
</style>
