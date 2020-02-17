<template>
  <div class="community-proof-form">
    <b-row>
      <b-col>
        <validation-observer ref="observer" v-slot="{ passes }">
          <b-form :novalidate="true" class="community-proof-form__form"
            @submit.stop.prevent="passes(submit)">
            <forms-file-uploader :label="community.name" field="proof" v-model="community.proof" />

            <div class="form__buttons">
              <b-button variant="success" type="submit">
                {{ $t('enregistrer') | capitalize }}
              </b-button>
            </div>
          </b-form>
        </validation-observer>
      </b-col>
      <b-col>
        {{ community.requirements }}
      </b-col>
    </b-row>
  </div>
</template>

<script>
import FormsFileUploader from '@/components/Forms/FileUploader.vue';

import locales from '@/locales';

export default {
  name: 'CommunityProofForm',
  components: {
    FormsFileUploader,
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
