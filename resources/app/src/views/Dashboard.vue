<template>
  <layout-page name="dashboard">
    <b-row class="page__section">
      <b-col class="page__content__main" lg="9">
        <h1>{{ $t('Bienvenue, {name}', { name: user.full_name })}}</h1>

        <section class="page__section" v-if="hasTutorials">
          <h2>Pour commencer</h2>

          <div class="page__section__tutorials">
            <div v-if="hasTutorial('discover-community')">
              <tutorial-block title="Découvre ta communauté"
                to="/community"
                bg-image="/img-tetes.png" variant="dark" />
            </div>

            <div v-if="hasTutorial('add-vehicle')">
              <tutorial-block title="Inscris un véhicule"
                to="/guide/inscris-un-vehicule"
                bg-image="/img-voiture.png" variant="dark" />
            </div>

            <div v-if="hasTutorial('find-vehicle')">
              <tutorial-block title="Trouve un véhicule"
                to="/guide/trouve-un-vehicule"
                bg-image="/img-vehicules.png" variant="light" />
            </div>
          </div>
        </section>

        <section class="page__section" v-if="hasWaitingLoans">
          <h2>Nouvelles demandes de réservation</h2>
          <p class="muted">Cette personne devrait entrer en contact avec vous sous peu.</p>

          <div class="dashboard__waiting-loans" v-for="loan in waitingLoans" :key="loan.id">
            {{ loan }}
          </div>
        </section>

        <section class="page__section" v-if="hasOngoingLoans">
          <h2>Réservations en cours</h2>

          <div class="dashboard__ongoing-loans" v-for="loan in ongoingLoans" :key="loan.id">
            {{ loan }}
          </div>
        </section>

        <section class="page__section">
          <h2>Réservations à venir</h2>

          <div class="dashboard__upcoming-loans" v-for="loan in upcomingLoans" :key="loan.id">
            {{ loan }}
          </div>
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
              Aucun véhicule.<br>
              Ajoutez-en un <router-link to="/profile/loanables/new">ici</router-link>.
            </div>
          </div>
        </section>
      </b-col>

      <b-col tag="aside" class="page__sidebar" lg="3">
        <b-card>
          <dashboard-balance :user="user" />

          <hr>

          <dashboard-loan-history :loans="pastLoans" />

          <hr>

          <dashboard-resources-list />
        </b-card>
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

import DashboardBalance from '@/components/Dashboard/Balance.vue';
import LoanableInfoBox from '@/components/Loanable/InfoBox.vue';
import DashboardLoanHistory from '@/components/Dashboard/LoanHistory.vue';
import DashboardResourcesList from '@/components/Dashboard/ResourcesList.vue';
import TutorialBlock from '@/components/Dashboard/TutorialBlock.vue';

export default {
  name: 'Dashboard',
  mixins: [Authenticated, Notification],
  components: {
    DashboardBalance,
    LoanableInfoBox,
    DashboardLoanHistory,
    DashboardResourcesList,
    TutorialBlock,
  },
  beforeMount() {
    if (!this.isLoggedIn) {
      this.skipToLogin('app');
    }
  },
  computed: {
    hasTutorials() {
      return this.hasTutorial('add-vehicle') || this.hasTutorial('find-vehicle')
        || this.hasTutorial('discover-community');
    },
  },
  methods: {
    hasTutorial(name) {
      switch (name) {
        case 'add-vehicle':
          return this.user.owner && this.user.loanables.length === 0;
        case 'find-vehicle':
          return !!this.user.borrower;
        case 'discover-community':
          return this.user.created_at
            >= this.$dayjs().subtract(2, 'week').format('YYYY-MM-DD HH:mm:ss');
        default:
          return false;
      }
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

  .page__content {
    padding-top: 45px;
  }

  &__vehicles {
    .loanable-info-box {
      margin-bottom: 20px;
    }
  }
}
</style>
