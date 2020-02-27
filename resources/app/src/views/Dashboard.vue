<template>
  <layout-page name="dashboard">
    <b-row class="page__section">
      <b-col class="page__content__main" lg="9">
        <h1>{{ $t('Bienvenue, {name}', { name: user.full_name })}}</h1>

        <section class="page__section">
          <h2>Pour commencer</h2>

          <div class="page__section__tutorials">
            <div v-if="user.owner && user.loanables.length === 0">
              <tutorial-block title="Inscrire un véhicule"
                to="/guide/inscrire-un-vehicule"
                bg-image="/img-tetes.png" variant="dark" />
            </div>
          </div>
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

        <section class="page__section" v-if="user.owner">
          <b-row>
            <b-col>
              <h2>Mes véhicules</h2>
            </b-col>
            <b-col class="text-right">
              <b-button variant="outline-primary" to="/profile/loanables">
                Gérer mes véhicules
              </b-button>
            </b-col>
          </b-row>

          <div class="dashboard__vehicles">
            <div v-if="user.loanables.length > 0">
              <loanable-info-box
               v-for="loanable in user.loanables" :key="loanable.id"
               v-bind="loanable" />
            </div>
            <div v-else>
              Aucun véhicule. Ajoutez-en un <router-link to="/profile/loanables/new">ici</router-link>.
            </div>
          </div>
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
import LoanableInfoBox from '@/components/Loanable/InfoBox.vue';
import LocationHistory from '@/components/Dashboard/LocationHistory.vue';
import ResourcesList from '@/components/Dashboard/ResourcesList.vue';
import TutorialBlock from '@/components/Dashboard/TutorialBlock.vue';

export default {
  name: 'Dashboard',
  mixins: [Authenticated, Notification],
  components: {
    AccountStatus,
    LoanableInfoBox,
    LocationHistory,
    ResourcesList,
    TutorialBlock,
  },
  beforeMount() {
    if (!this.isLoggedIn) {
      this.skipToLogin('app');
    }
  },
};
</script>

<style lang="scss">
.dashboard {
  .page__section {
    h2 {
      margin-bottom: 25px;
    }
  }

  &__vehicles {
    .loanable-info-box {
      margin-bottom: 20px;
    }
  }
}
</style>
