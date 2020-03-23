<template>
  <div class="profile-loanables" v-if="routeDataLoaded">
    <div v-if="user.owner">
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
      <b-row v-else v-for="loanable in data" :key="loanable.id"
        class="profile-loanables__loanables">
        <b-col class="profile-loanables__loanables__loanable">
          <loanable-info-box :buttons="['remove']" v-bind="loanable" />
        </b-col>
      </b-row>
    </div>
    <div v-else>
      <p>
        Vous désirez mettre un véhicule à disposition de la communauté?
        <a href="#" @click="createOwnerProfile">Cliquez ici</a> pour commencer!
      </p>
    </div>
  </div>
  <layout-loading v-else />
</template>

<script>
import LoanableInfoBox from '@/components/Loanable/InfoBox.vue';

import Authenticated from '@/mixins/Authenticated';
import DataRouteGuards from '@/mixins/DataRouteGuards';
import ListMixin from '@/mixins/ListMixin';

import locales from '@/locales';

export default {
  name: 'ProfileLoanables',
  mixins: [Authenticated, DataRouteGuards, ListMixin],
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
  methods: {
    async createOwnerProfile() {
      await this.$store.dispatch('users/update', {
        id: this.user.id,
        data: {
          id: this.user.id,
          owner: {},
        },
        params: {},
      });
      await this.$store.dispatch('loadUser');
    },
  },
};
</script>

<style lang="scss">
.profile-loanables__loanables__loanable {
  margin-bottom: 20px;
}
</style>
