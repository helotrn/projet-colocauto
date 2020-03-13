<template>
  <b-container class="profile-loanable" fluid v-if="item && routeDataLoaded">
    <vue-headful :title="fullTitle" />

    <b-row>
      <b-col>
        <h1 v-if="item.name">{{ item.name }}</h1>
        <h1 v-else><em>{{ $t('nouveau') | capitalize }} {{ $tc('véhicule', 1) }}</em></h1>
      </b-col>
    </b-row>

    <b-row>
      <b-col>
        <loanable-form :loanable="item" :form="form" :loading="loading"
          @submit="submitLoanable" :show-reset="!!item.id" :changed="changed"
          @reset="reset" :center="center" />
      </b-col>
    </b-row>
  </b-container>
  <layout-loading v-else />
</template>

<script>
import LoanableForm from '@/components/Loanable/Form.vue';

import DataRouteGuards from '@/mixins/DataRouteGuards';
import FormMixin from '@/mixins/FormMixin';

import locales from '@/locales';

import { capitalize } from '@/helpers/filters';

export default {
  name: 'ProfileLoanable',
  mixins: [DataRouteGuards, FormMixin],
  components: {
    LoanableForm,
  },
  computed: {
    communityCenter() {
      if (!this.item.community) {
        return null;
      }

      return this.item.community.center;
    },
    center: {
      get() {
        if (this.$store.state['profile.loanable'].center) {
          return this.$store.state['profile.loanable'].center;
        }

        return this.communityCenter || this.averageCommunitiesCenter;
      },
      set(center) {
        this.$store.commit('register.map/center', center);
      },
    },
    fullTitle() {
      const parts = [
        'LocoMotion',
        capitalize(this.$i18n.t('titles.profile')),
        capitalize(this.$i18n.tc('véhicule', 2)),
      ];

      if (this.pageTitle) {
        parts.push(this.pageTitle);
      }

      return parts.reverse().join(' | ');
    },
    averageCommunitiesCenter() {
      const { communities: { data: communities } } = this.$store.state;

      const center = communities.reduce((acc, c) => [
        (acc[0] + c.center[0]) / 2,
        (acc[1] + c.center[1]) / 2,
      ], communities[0].center);
      return {
        lat: center[0],
        lng: center[1],
      };
    },
    pageTitle() {
      return this.item.name || capitalize(this.$i18n.tc('véhicule', 1));
    },
  },
  methods: {
    async submitLoanable() {
      await this.submit();
      await this.$store.dispatch('loadUser');
    },
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.loanables,
        ...locales.en.forms,
        profile: { ...locales.en.profile },
        titles: { ...locales.en.titles },
      },
      fr: {
        ...locales.fr.loanables,
        ...locales.fr.forms,
        profile: { ...locales.fr.profile },
        titles: { ...locales.fr.titles },
      },
    },
  },
};
</script>

<style lang="scss">
</style>
