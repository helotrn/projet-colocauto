<template>
  <b-container fluid v-if="item">
    <vue-headful :title="fullTitle" />

    <b-row>
      <b-col>
        <h1 v-if="item.name">{{ item.name }}</h1>
        <h1 v-else><em>{{ $tc('facture', 1) | capitalize }}</em></h1>
      </b-col>
    </b-row>

    <b-row>
      <b-col>
        <validation-observer ref="observer" v-slot="{ passes }">
          <b-form class="form" @submit.prevent="passes(submit)">

            <div class="form__buttons">
              <b-button-group>
                <b-button variant="success" type="submit" :disabled="!changed">
                  Sauvegarder
                </b-button>
                <b-button type="reset" :disabled="!changed" @click="reset">
                  Réinitialiser
                </b-button>
              </b-button-group>
            </div>
          </b-form>
        </validation-observer>
      </b-col>
    </b-row>
  </b-container>
  <layout-loading v-else />
</template>

<script>
import FormMixin from '@/mixins/FormMixin';

import locales from '@/locales';

import { capitalize } from '@/helpers/filters';

export default {
  name: 'AdminInvoice',
  mixins: [FormMixin],
  computed: {
    fullTitle() {
      const parts = [
        'LocoMotion',
        capitalize(this.$i18n.t('titles.admin')),
        capitalize(this.$i18n.tc('facture', 2)),
      ];

      if (this.pageTitle) {
        parts.push(this.pageTitle);
      }

      return parts.reverse().join(' | ');
    },
    pageTitle() {
      return this.item.name || capitalize(this.$i18n.tc('facture', 1));
    },
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.invoices,
        ...locales.en.forms,
        titles: locales.en.titles,
      },
      fr: {
        ...locales.fr.invoices,
        ...locales.fr.forms,
        titles: locales.fr.titles,
      },
    },
  },
};
</script>

<style lang="scss">
</style>
