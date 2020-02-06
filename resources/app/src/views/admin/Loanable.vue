<template>
  <b-container fluid v-if="item">
    <vue-headful :title="fullTitle" />

    <b-row>
      <b-col>
        <h1 v-if="item.name">{{ item.name }}</h1>
        <h1 v-else><em>{{ $tc('véhicule', 1) | capitalize }}</em></h1>
      </b-col>
    </b-row>

    <b-row>
      <b-col>
        <b-form class="form" @submit.prevent="submit">
          <div class="form__section">
            <h2>Informations</h2>

            <admin-form :definition="form.general" :item="item" entity="loanables" />
          </div>

          <div class="form__section" v-if="item.type === 'bike'">
            <h2>Détails du vélo</h2>

            <admin-form :definition="form.bike" :item="item" entity="bikes" />
          </div>
          <div class="form__section" v-else-if="item.type === 'car'">
            <h2>Détails de la voiture</h2>

            <admin-form :definition="form.car" :item="item" entity="cars" />
          </div>
          <div class="form__section" v-else-if="item.type === 'trailer'">
            <h2>Détails de la remorque</h2>

            <admin-form :definition="form.trailer" :item="item" entity="trailers" />
          </div>

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
      </b-col>
    </b-row>
  </b-container>
</template>

<script>
import AdminForm from '@/components/Admin/Form.vue';

import FormMixin from '@/mixins/FormMixin';

import locales from '@/locales';

import filters from '@/helpers/filters';

const { capitalize } = filters;

export default {
  name: 'AdminLoanable',
  mixins: [FormMixin],
  components: {
    AdminForm,
  },
  computed: {
    fullTitle() {
      const parts = [
        'LocoMotion',
        capitalize(this.$i18n.t('titles.admin')),
        capitalize(this.$i18n.tc('véhicule', 2)),
      ];

      if (this.pageTitle) {
        parts.push(this.pageTitle);
      }

      return parts.reverse().join(' | ');
    },
    pageTitle() {
      return this.item.name || capitalize(this.$i18n.tc('véhicule', 1));
    },
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.loanables,
        ...locales.en.forms,
        titles: locales.en.titles,
      },
      fr: {
        ...locales.fr.loanables,
        ...locales.fr.forms,
        titles: locales.fr.titles,
      },
    },
  },
};
</script>

<style lang="scss">
</style>
