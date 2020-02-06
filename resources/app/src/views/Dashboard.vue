<template>
  <layout-page name="dashboard">
    <b-row class="page__section">
      <b-col class="page__content__main" lg="9">
        <h1>{{ $t('Bienvenue, {name}', { name: user.full_name })}}</h1>

        <section class="page__section">
          <h2>Pour commencer</h2>

          <div>Ici des blocs de tutoriels selon les tags appliqués à l'utilisateur.</div>
        </section>

        <section class="page__section">
          <h2>Nouvelles demandes de réservation</h2>

          <div>Ici des blocs de nouvelles réservations.</div>
        </section>

        <section class="page__section">
          <h2>Réservations en cours</h2>

          <div>Ici des blocs de réservations en cours.</div>
        </section>

        <section class="page__section">
          <h2>Réservations à venir</h2>

          <div>Ici des blocs de réservations à venir.</div>
        </section>

        <section class="page__section">
          <h2>Mes véhicules</h2>

          <div>Ici des blocs de réservations à venir.</div>
        </section>
      </b-col>

      <b-col tag="aside" class="page__sidebar" lg="3">
        <account-status />

        <location-history />

        <resources-list />
      </b-col>
    </b-row>
  </layout-page>
</template>

<i18n>
fr:
  'Bienvenue, {name}': Bienvenue, {name}
en:
  'Bienvenue, {name}': Welcome, {name}
</i18n>

<script>
import Authenticated from '@/mixins/Authenticated';
import Notification from '@/mixins/Notification';

import AccountStatus from '@/components/Dashboard/AccountStatus.vue';
import LocationHistory from '@/components/Dashboard/LocationHistory.vue';
import ResourcesList from '@/components/Dashboard/ResourcesList.vue';

export default {
  name: 'Dashboard',
  mixins: [Authenticated, Notification],
  components: {
    AccountStatus,
    LocationHistory,
    ResourcesList,
  },
  beforeMount() {
    if (!this.isLoggedIn) {
      this.skipToLogin('app');
    }
  },
};
</script>

<style lang="scss">
</style>
