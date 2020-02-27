<template>
  <div class="community-proof-form">
    <b-row>
      <b-col>
        <validation-observer ref="observer" v-slot="{ passes }">
          <b-form :novalidate="true" class="community-proof-form__form"
            @submit.stop.prevent="passes(submit)">
            <forms-image-uploader
              :label="community.name"
              field="proof"
              v-model="community.proof" />

            <div class="form__buttons">
              <b-button variant="success" type="submit">
                {{ $t('enregistrer') | capitalize }}
              </b-button>
            </div>
          </b-form>
        </validation-observer>
      </b-col>
      <b-col>
        <div class="community-proof-form__instructions">
          <p>
            Envoyez un relevé de compte, un bail, ou tout autre document indiquant
            votre adresse et votre nom.
          </p>
          <p>
            Soit en fichier numérisé, ou simplement une photo prise avec votre téléphone.
          </p>
        </div>

        <div class="community-proof-form__requirements">
          {{ community.requirements }}
        </div>
      </b-col>
    </b-row>
  </div>
</template>

<script>
import FormsImageUploader from '@/components/Forms/ImageUploader.vue';

import locales from '@/locales';

export default {
  name: 'CommunityProofForm',
  components: {
    FormsImageUploader,
  },
  props: {
    community: {
      type: Object,
      required: true,
    },
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.communities,
        ...locales.en.forms,
      },
      fr: {
        ...locales.fr.communities,
        ...locales.fr.forms,
      },
    },
  },
  methods: {
    submit(...params) {
      this.$emit('submit', ...params);
    },
  },
};
</script>

<style lang="scss">
</style>
