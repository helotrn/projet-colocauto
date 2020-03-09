<template>
  <b-container fluid v-if="item">
    <b-row>
      <b-col>
        <h1 v-if="item.name">{{ item.name }}</h1>
        <h1 v-else><em>{{ $tc('membre', 1) | capitalize }}</em></h1>
      </b-col>
    </b-row>

    <b-row>
      <b-col>
        <b-form class="form" @submit.prevent="submit">
          <div class="form__section">
            <h2>Informations générales</h2>

            <forms-builder :definition="form.general" :item="item" entity="users" />
          </div>

          <div class="form__section">
            <h2>Profil d'emprunteur</h2>

            <forms-builder :definition="form.borrower" :item="item" entity="borrowers" />
          </div>

          <div class="form__section">
            <h2>Véhicules</h2>

            <b-table
              striped hover :items="item.loanables"
              selectable select-mode="multi" @row-selected="loanableRowSelected"
              :fields="loanableTable" no-sort-reset
              :show-empty="true" empty-text="Pas de véhicules">
              <template v-slot:cell(actions)="row">
                <div class="text-right">
                  <b-button size="sm" :to="`/admin/loanables/${row.item.id}`">
                    {{ $t('modifier') | capitalize }}
                  </b-button>
                </div>
              </template>
            </b-table>
          </div>

          <div class="form__section">
            <h2>Communautés</h2>

            <b-table
              striped hover :items="item.communities"
              selectable select-mode="multi" @row-selected="communityRowSelected"
              :fields="communityTable" no-sort-reset
              :show-empty="true" empty-text="Pas de communauté">
              <template v-slot:cell(actions)="row">
                <div class="text-right">
                  <b-button size="sm" :to="`/admin/communities/${row.item.id}#members`">
                    {{ $t('modifier') | capitalize }}
                  </b-button>
                </div>
              </template>
            </b-table>
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
  <layout-loading v-else />
</template>

<script>
import FormsBuilder from '@/components/Forms/Builder.vue';

import FormMixin from '@/mixins/FormMixin';

import locales from '@/locales';

export default {
  name: 'AdminUser',
  mixins: [FormMixin],
  components: {
    FormsBuilder,
  },
  data() {
    return {
      communitiesSelected: [],
      communityTable: [
        { key: 'id', label: 'ID', sortable: true },
        { key: 'name', label: 'Nom', sortable: true },
        { key: 'actions', label: 'Actions', tdClass: 'table__cell__actions' },
      ],
      loanablesSelected: [],
      loanableTable: [
        { key: 'id', label: 'ID', sortable: true },
        { key: 'name', label: 'Nom', sortable: true },
        { key: 'actions', label: 'Actions', tdClass: 'table__cell__actions' },
      ],
    };
  },
  methods: {
    communityRowSelected(rows) {
      this.communitiesSelected = rows;
    },
    loanableRowSelected(rows) {
      this.loanablesSelected = rows;
    },
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.users,
        ...locales.en.forms,
      },
      fr: {
        ...locales.fr.users,
        ...locales.fr.forms,
      },
    },
  },
};
</script>

<style lang="scss">
</style>
