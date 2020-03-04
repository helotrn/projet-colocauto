<template>
  <div class="profile-loanables">
    <b-row>
      <b-col class="admin__buttons">
        <b-btn v-if="creatable" :to="`/profile/${slug}/new`">
          {{ $t('ajouter un véhicule') | capitalize }}
        </b-btn>
      </b-col>
    </b-row>

    <b-row v-if="data.length === 0">
      <b-col>
        Pas de véhicule.
      </b-col>
    </b-row>

    <b-row v-else v-for="loanable in data" :key="loanable.id">
      <b-col>
        <loanable-info-box
          v-for="loanable in data" :key="loanable.id"
          v-bind="loanable" />
      </b-col>
    </b-row>
  </div>
</template>

<script>
import LoanableInfoBox from '@/components/Loanable/InfoBox.vue';

import DataRouteGuards from '@/mixins/DataRouteGuards';
import ListMixin from '@/mixins/ListMixin';

import locales from '@/locales';

export default {
  name: 'ProfileLoanables',
  mixins: [DataRouteGuards, ListMixin],
  components: { LoanableInfoBox },
  data() {
    return {
      selected: [],
      fields: [
        { key: 'id', label: 'ID', sortable: true },
        { key: 'name', label: 'Nom', sortable: true },
        { key: 'type', label: 'Type', sortable: false },
        { key: 'actions', label: 'Actions', tdClass: 'table__cell__actions' },
      ],
    };
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
};
</script>

<style lang="scss">
</style>
