<template>
  <div class="loanable-form">
    <validation-observer ref="observer" v-slot="{ valid, passes }">
      <b-form :novalidate="true" class="form loanable-form__form"
        @submit.stop.prevent="passes(submit)">
        <div class="form__section">
          <h2>Informations</h2>

          <b-row>
            <b-col lg="8">
              <forms-validated-input name="name" :label="$t('fields.name') | capitalize"
                :rules="form.general.name.rules" type="text"
                :placeholder="placeholderOrLabel('name') | capitalize"
                v-model="loanable.name" />
            </b-col>

            <b-col lg="4">
              <forms-validated-input name="type" :label="$t('fields.type') | capitalize"
                :rules="form.general.type.rules" type="select" :options="form.general.type.options"
                :placeholder="placeholderOrLabel('type') | capitalize"
                :disabled="!!loanable.id"
                disabled-tooltip="On ne peut pas changer le type d'un véhicule existant."
                v-model="loanable.type" />
            </b-col>
          </b-row>

          <b-row>
            <b-col lg="8">
              <forms-validated-input name="position"
                :description="form.general.position.description"
                :rules="form.general.position.rules" :center="center"
                :label="$t('fields.position') | capitalize" type="point"
                :placeholder="placeholderOrLabel('position') | capitalize"
                v-model="loanable.position" />
            </b-col>

            <b-col>
              <forms-validated-input name="location_description"
                :description="form.general.location_description.description"
                :rules="form.general.location_description.rules" :rows="12"
                :label="$t('fields.location_description') | capitalize" type="textarea"
                :placeholder="placeholderOrLabel('location_description') | capitalize"
                v-model="loanable.location_description" />
            </b-col>
          </b-row>

          <b-row>
            <b-col lg="4">
              <forms-image-uploader field="image"
                :label="$t('fields.image') | capitalize"
                v-model="loanable.image" />
            </b-col>

            <b-col lg="8">
              <forms-validated-input name="comments"
                :description="form.general.comments.description"
                :rules="form.general.comments.rules"
                :label="$t('fields.comments') | capitalize" type="textarea"
                :placeholder="placeholderOrLabel('comments') | capitalize"
                v-model="loanable.comments" />

              <forms-validated-input name="instructions"
                :description="form.general.instructions.description"
                :rules="form.general.instructions.rules"
                :label="$t('fields.instructions') | capitalize" type="textarea"
                :placeholder="placeholderOrLabel('instructions') | capitalize"
                v-model="loanable.instructions" />
            </b-col>
          </b-row>
        </div>

        <div class="form__section" v-if="loanable.type === 'bike'">
          <h2>Détails du vélo</h2>

          <forms-builder :definition="form.bike" :item="loanable" entity="loanables" />
        </div>
        <div class="form__section" v-else-if="loanable.type === 'car'">
          <h2>Détails de la voiture</h2>

          <forms-builder :definition="form.car" :item="loanable" entity="loanables" />
        </div>
        <div class="form__section" v-else-if="loanable.type === 'trailer'">
          <h2>Détails de la remorque</h2>

          <forms-builder :definition="form.trailer" :item="loanable" entity="loanables" />
        </div>
        <div class="form__section text-center" v-else>
          <span>
            Sélectionnez un type de véhicule pour poursuivre la configuration.
          </span>
        </div>

        <div class="form__section" v-if="loanable.type && loanable.id">
          <a id="availability" />
          <loanable-availability-calendar
            :loanable="loanable"
            :loading="loading" />
        </div>

        <div class="form__buttons" v-if="!hideButtons">
          <b-button-group v-if="showReset">
            <b-button variant="success" type="submit" :disabled="!changed">
              {{ $t('enregistrer') | capitalize }}
            </b-button>
            <b-button type="reset" :disabled="!changed" @click="$emit('reset')">
              {{ $t('réinitialiser') | capitalize }}
            </b-button>
          </b-button-group>
          <b-button variant="success" type="submit" v-else>
            {{ $t('enregistrer') | capitalize }}
          </b-button>
        </div>
      </b-form>
    </validation-observer>
  </div>
</template>

<script>
import FormsBuilder from '@/components/Forms/Builder.vue';
import FormsValidatedInput from '@/components/Forms/ValidatedInput.vue';
import LoanableAvailabilityCalendar from '@/components/Loanable/AvailabilityCalendar.vue';
import FormsImageUploader from '@/components/Forms/ImageUploader.vue';

import locales from '@/locales';

export default {
  name: 'LoanableForm',
  components: {
    FormsBuilder,
    FormsImageUploader,
    FormsValidatedInput,
    LoanableAvailabilityCalendar,
  },
  props: {
    center: {
      type: Object,
      required: true,
    },
    form: {
      type: Object,
      required: false,
      default() {
        return null;
      },
    },
    changed: {
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
    loanable: {
      type: Object,
      required: true,
    },
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.loanables,
        ...locales.en.forms,
      },
      fr: {
        ...locales.fr.loanables,
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
      const ownerId = this.$store.state.user.owner.id;
      this.$store.commit('loanables/mergeItem', { owner: { id: ownerId } });
      this.$emit('submit', ...params);
    },
  },
};
</script>

<style lang="scss">
</style>
