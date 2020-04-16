<template>
  <b-container fluid v-if="item">
    <b-row>
      <b-col>
        <h1 v-if="item.name">{{ item.name }}</h1>
        <h1 v-else><em>{{ $tc('mot-clé', 1) | capitalize }}</em></h1>
      </b-col>
    </b-row>

    <b-row>
      <b-col>
        <b-form class="form" @submit.prevent="submit">
          <forms-builder :definition="form" v-model="item" entity="tags" />

          <div class="form__buttons">
            <b-button-group>
              <b-button variant="success" type="submit" :disabled="!changed || loading">
                Sauvegarder
              </b-button>
              <b-button type="reset" :disabled="!changed" @click="reset">
                Réinitialiser
              </b-button>
            </b-button-group>
          </div>
        </b-form>
      </b-col>
    </b-row>
  </b-container>
  <layout-loading v-else />
</template>

<script>
import FormsBuilder from '@/components/Forms/Builder.vue';

import FormMixin from '@/mixins/FormMixin';

import locales from '@/locales';

export default {
  name: 'AdminTag',
  mixins: [FormMixin],
  components: {
    FormsBuilder,
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.tags,
        ...locales.en.forms,
      },
      fr: {
        ...locales.fr.tags,
        ...locales.fr.forms,
      },
    },
  },
};
</script>

<style lang="scss">
</style>
