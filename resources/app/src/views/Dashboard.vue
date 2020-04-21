<template>
  <layout-page name="dashboard">
    <b-row class="page__section">
      <b-col class="page__content__main" xl="9" lg="8" md="7">
        <h1>{{ $t('Bienvenue, {name}', { name: user.name })}}</h1>

        <section class="page__section">
          <b-jumbotron bg-variant="warning"
            header="Mise-à-jour COVID-19" lead="LocoMotion est avec vous">
            <b-button v-b-modal="'covid-modal'">En savoir plus</b-button>
          </b-jumbotron>

          <b-modal size="md" class="covid-modal"
            title="COVID-19: Quelques informations importantes"
            id="covid-modal" footer-class="d-none">
            <p class="covid-modal__subtitle">VOUS ÊTES MALADE OU VOUS REVENEZ DE VOYAGE?</p>
            <p>→  N’UTILISEZ PAS LOCOMOTION</p>

            <p class="covid-modal__subtitle">POUR LES PROPRIÉTAIRES D'AUTO</p>
            <p>
              Si vous souhaitez retirer temporairement votre auto,
              pensez à mettre à jour votre calendrier.
            </p>

            <p class="covid-modal__subtitle">POUR TOU-TE-S LES PARTICIPANT-E-S</p>
            <p>
              Avant et après l’utilisation d’une voiture, LAVEZ VOS MAINS à l’eau courante
              tiède et au savon pendant au moins 20 secondes ou utilisez un désinfectant à
              base d’alcool.
            </p>

            <p class="covid-modal__subtitle">GARDEZ LES AUTOS PROPRES</p>
            <p>
              Que ce soit votre autre ou celle de votre voisin-e, voici quelques
              recommandations à prendre avant et après son utilisation:
            </p>
            <ul>
              <li>
                Nettoyez le volant et autres surfaces avec un linge et du désinfectant.
              </li>
              <li>
                Évitez plus que jamais de laisser tout déchet
                (mouchoir, tasse, emballage, etc…)
              </li>
            </ul>
          </b-modal>
        </section>

        <section class="page__section" v-if="!hasCompletedRegistration">
          <b-button to="/register">Compléter l'inscription</b-button>
        </section>

        <section class="page__section" v-if="hasTutorials">
          <h2>Pour commencer</h2>

          <div class="page__section__tutorials">
            <div v-if="hasTutorial('discover-community')">
              <tutorial-block title="Découvrez votre voisinage"
                to="/community"
                bg-image="/img-tetes.png" variant="dark" />
            </div>

            <div v-if="hasTutorial('add-vehicle')">
              <tutorial-block title="Inscrivez un véhicule"
                to="/profile/loanables/new"
                bg-image="/img-voiture.png" variant="dark" />
            </div>

            <div v-if="hasTutorial('find-vehicle')">
              <tutorial-block title="Trouvez un véhicule"
                to="/community/list"
                bg-image="/img-vehicules.png" variant="light" />
            </div>
          </div>
        </section>

        <section class="page__section" v-if="hasWaitingLoans">
          <h2>Nouvelles demandes d'emprunt</h2>

          <div class="dashboard__waiting-loans" v-for="loan in waitingLoans" :key="loan.id">
            <loan-info-box :loan="loan" :user="user" />
          </div>
        </section>

        <section class="page__section" v-if="hasOngoingLoans">
          <h2>Emprunts en cours</h2>

          <div class="dashboard__ongoing-loans" v-for="loan in ongoingLoans" :key="loan.id">
            <loan-info-box :loan="loan" :user="user" :buttons="['view']" />
          </div>
        </section>

        <section class="page__section" v-if="hasUpcomingLoans">
          <h2>Emprunts à venir</h2>

          <div class="dashboard__upcoming-loans" v-for="loan in upcomingLoans" :key="loan.id">
            <loan-info-box mode="upcoming"
              :loan="loan" :user="user" :buttons="['view', 'cancel']" />
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

      <b-col tag="aside" class="page__sidebar" xl="3" lg="4" md="5">
        <b-card>
          <div v-if="hasCompletedRegistration">
            <dashboard-balance :user="user" />

            <hr>
          </div>

          <div v-if="hasCompletedRegistration">
            <dashboard-loan-history
              :past-loans="pastLoans.slice(0, 3)"
              :upcoming-loans="upcomingLoans.slice(0, 3)"
              :ongoing-loans="ongoingLoans.slice(0, 3)"
              :waiting-loans="waitingLoans.slice(0, 3)"
              :borrower="user.borrower" />

            <hr>
          </div>

          <dashboard-resources-list :has-community="hasCommunity" />
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

import DashboardBalance from '@/components/Dashboard/Balance.vue';
import LoanInfoBox from '@/components/Loan/InfoBox.vue';
import LoanableInfoBox from '@/components/Loanable/InfoBox.vue';
import DashboardLoanHistory from '@/components/Dashboard/LoanHistory.vue';
import DashboardResourcesList from '@/components/Dashboard/ResourcesList.vue';
import TutorialBlock from '@/components/Dashboard/TutorialBlock.vue';

export default {
  name: 'Dashboard',
  mixins: [Authenticated],
  components: {
    DashboardBalance,
    LoanInfoBox,
    LoanableInfoBox,
    DashboardLoanHistory,
    DashboardResourcesList,
    TutorialBlock,
  },
  beforeMount() {
    if (!this.isLoggedIn) {
      this.skipToLogin();
    }

    if (!this.user.name) {
      this.$router.replace('/register');
    }

    if (this.user.role === 'admin') {
      this.$router.replace('/admin');
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
          return !!this.user.borrower && this.hasCommunity;
        case 'discover-community':
          return this.hasCommunity && this.user.created_at
            >= this.$dayjs().subtract(2, 'week').format('YYYY-MM-DD HH:mm:ss');
        default:
          return false;
      }
    },
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
    padding-bottom: 45px;

    &__main > h1 {
      margin-bottom: 60px;
    }
  }

  &__vehicles {
    .loanable-info-box {
      margin-bottom: 20px;
    }
  }
}
</style>
