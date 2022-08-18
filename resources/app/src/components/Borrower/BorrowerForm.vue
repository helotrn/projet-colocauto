<template>
  <div class="borrower-form">
    <validation-observer ref="observer" v-slot="{ passes }">
      <b-form :novalidate="true" class="borrower-form__form" @submit.stop.prevent="passes(submit)">
        <b-row>
          <b-col>
            <forms-validated-input
              name="drivers_license_number"
              type="text"
              :disabled="disabled"
              :label="$t('fields.drivers_license_number') | capitalize"
              :placeholder="placeholderOrLabel('drivers_license_number') | capitalize"
              v-model="borrower.drivers_license_number"
            />
          </b-col>
        </b-row>

        <b-row>
          <b-col>
            <forms-validated-input
              name="has_not_been_sued_last_ten_years"
              type="checkbox"
              :disabled="disabled"
              label="Je confirme n'avoir reçu aucune poursuite au cours des 10 dernières années"
              v-model="hasNotBeenSuedLastTenYears"
            />
          </b-col>
        </b-row>

        <b-row>
          <b-col>
            <forms-file-uploader
              field="saaq"
              :disabled="disabled"
              :label="$t('fields.saaq') | capitalize"
              placeholder="Ex.: monfichier.pdf"
              v-model="borrower.saaq"
            />
          </b-col>
        </b-row>

        <b-row>
          <b-col>
            <forms-file-uploader
              field="gaa"
              :disabled="disabled"
              :label="$t('fields.gaa') | capitalize"
              placeholder="Ex.: monfichier.pdf"
              v-model="borrower.gaa"
            />
          </b-col>
        </b-row>

        <b-row>
          <b-col>
            <div>
              <p>Vous ne les avez pas sous la main ? Pas de soucis.</p>

              <ul>
                <li>
                  <a :href="saaqUrl" target="_blank">
                    Pour commander votre dossier de conduite SAAQ
                  </a>
                </li>
                <li>
                  <a :href="gaaUrl" target="_blank">
                    Pour commander votre rapport de sinistre GAA
                  </a>
                </li>
              </ul>
            </div>
          </b-col>
        </b-row>

        <div class="form__buttons" v-if="!hideButtons">
          <b-button-group v-if="showReset">
            <b-button
              variant="success"
              type="submit"
              :disabled="!changed || !hasNotBeenSuedLastTenYears"
            >
              {{ $t("enregistrer") | capitalize }}
            </b-button>
            <b-button type="reset" :disabled="!changed" @click="$emit('reset')">
              {{ $t("réinitialiser") | capitalize }}
            </b-button>
          </b-button-group>
          <b-button variant="success" type="submit" v-else>
            {{ $t("enregistrer") | capitalize }}
          </b-button>
        </div>
      </b-form>
    </validation-observer>
  </div>
</template>

<script>
import FormsValidatedInput from "@/components/Forms/ValidatedInput.vue";
import FormsFileUploader from "@/components/Forms/FileUploader.vue";

import FormLabelsMixin from "@/mixins/FormLabelsMixin";

import locales from "@/locales";

export default {
  name: "BorrowerForm",
  mixins: [FormLabelsMixin],
  components: {
    FormsFileUploader,
    FormsValidatedInput,
  },
  props: {
    borrower: {
      type: Object,
      required: true,
    },
    changed: {
      type: Boolean,
      required: false,
      default: false,
    },
    disabled: {
      type: Boolean,
      required: false,
      default: false,
    },
    hideButtons: {
      type: Boolean,
      required: false,
    },
    loading: {
      type: Boolean,
      required: true,
    },
    showReset: {
      type: Boolean,
      required: false,
      default: false,
    },
  },
  data() {
    return {
      gaaUrl: "https://mondossier.gaa.qc.ca/fr/DemandeWeb/DemandeReleve/",
      saaqUrl: "https://services.saaq.gouv.qc.ca/FonctionsWeb/EtatDossierConduite.Web/",
    };
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
    submit(...params) {
      this.$emit("submit", ...params);
    },
  },
  computed: {
    hasNotBeenSuedLastTenYears: {
      get() {
        return this.borrower.has_not_been_sued_last_ten_years;
      },
      set(val) {
        this.borrower.has_not_been_sued_last_ten_years = val;
      },
    },
  },
};
</script>

<style lang="scss"></style>
