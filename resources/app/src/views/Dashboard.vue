<template>
  <layout-page name="dashboard">
    <b-row class="page__section">
      <b-col class="page__content__main" lg="9">
        <h1>Bienvenue, {{ user.full_name }}</h1>

        <section class="page__content__section">
          <h2>Pour commencer</h2>

          <div>Ici des blocs de tutoriels selon les tags appliqués à l'utilisateur.</div>
        </section>

        <section class="page__content__section">
          <h2>Nouvelles demandes de réservation</h2>

          <div>Ici des blocs de nouvelles réservations.</div>
        </section>

        <section class="page__content__section">
          <h2>Réservations en cours</h2>

          <div>Ici des blocs de réservations en cours.</div>
        </section>

        <section class="page__content__section">
          <h2>Réservations à venir</h2>

          <div>Ici des blocs de réservations à venir.</div>
        </section>

        <section class="page__content__section">
          <h2>Mes véhicules</h2>

          <div>Ici des blocs de réservations à venir.</div>
        </section>
      </b-col>

      <b-col tag="aside" class="page__content__sidebar" lg="3">
        <account-status />

        <location-history />

          <div class="page__content__sidebar__resources">
            <h3>Ressources</h3>

            <ul>
              <li class="page__content__sidebar__resources__resource">
                Foire aux questions (FAQ)
              </li>
            </ul>
          </div>
      </b-col>
    </b-row>
  </layout-page>
</template>

<script>
import Authenticated from '@/mixins/Authenticated';
import Notification from '@/mixins/Notification';

export default {
  name: 'Dashboard',
  mixins: [Authenticated, Notification],
  beforeMount() {
    if (!this.loggedIn) {
      this.skipToLogin('app');
    }
  }
};
</script>

<style lang="scss">
</style>
