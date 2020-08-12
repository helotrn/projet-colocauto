<template>
  <b-container fluid v-if="item && (loadedFullLoanable || !item.id)">
    <vue-headful :title="fullTitle" />

    <div v-if="item.id">
      <loan-header :user="user" :loan="item" />

      <loan-actions :item="item" @load="loadItem" :form="form"
        :user="user" @submit="submit" />
    </div>
    <div v-else-if="form">
      <b-row>
        <b-col>
          <h1 v-if="item.name">{{ item.name }}</h1>
          <h1 v-else><em>{{ $tc('model_name', 1) | capitalize }}</em></h1>
        </b-col>
      </b-row>

      <b-row>
        <b-col>
          <validation-observer ref="observer" v-slot="{ passes, valid }">
            <b-form class="form" @submit.prevent="passes(submitAndReload)">
              <forms-builder :definition="form" v-model="item" entity="loans" />

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
    </div>
  </b-container>
  <layout-loading v-else />
</template>

<script>
import FormsBuilder from '@/components/Forms/Builder.vue';
import LoanActions from '@/components/Loan/Actions.vue';
import LoanHeader from '@/components/Loan/LoanHeader.vue';

import Authenticated from '@/mixins/Authenticated';
import FormMixin from '@/mixins/FormMixin';
import LoanStepsSequence from '@/mixins/LoanStepsSequence';

import locales from '@/locales';

import { capitalize } from '@/helpers/filters';

export default {
  name: 'AdminLoan',
  mixins: [Authenticated, FormMixin, LoanStepsSequence],
  components: {
    FormsBuilder,
    LoanActions,
    LoanHeader,
  },
  data() {
    return {
      loadedFullLoanable: false,
    };
  },
  computed: {
    fullTitle() {
      const parts = [
        'LocoMotion',
        capitalize(this.$i18n.t('titles.admin')),
        capitalize(this.$i18n.tc('model_name', 2)),
      ];

      if (this.pageTitle) {
        parts.push(this.pageTitle);
      }

      return parts.reverse().join(' | ');
    },
    pageTitle() {
      return this.item.name || capitalize(this.$i18n.tc('model_name', 1));
    },
  },
  methods: {
    async formMixinCallback() {
      if (this.item.id) {
        const { id, type } = this.item.loanable;
        await this.$store.dispatch(`${type}s/retrieveOne`, {
          params: {
            fields: '*,owner.id,owner.user.id,owner.user.avatar,owner.user.name',
            '!fields': 'events',
          },
          id,
        });
        const loanable = this.$store.state[`${type}s`].item;

        this.$store.commit(`${type}s/item`, null);

        this.$store.commit(`${this.slug}/mergeItem`, { loanable });
      }

      this.loadedFullLoanable = true;
    },
    async submitAndReload() {
      await this.submit();
      await this.formMixinCallback();
    },
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.loans,
        ...locales.en.forms,
        titles: locales.en.titles,
      },
      fr: {
        ...locales.fr.loans,
        ...locales.fr.forms,
        titles: locales.fr.titles,
      },
    },
  },
};
</script>

<style lang="scss">
</style>
