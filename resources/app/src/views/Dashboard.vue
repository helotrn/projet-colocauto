<template>
  <layout-page name="dashboard" wide>
    <b-container>
      <b-row class="page__section page__section__main">
        <b-col class="page__content" xl="9" lg="8" md="7">
          <h1>{{ $t('Bienvenue, {name}', { name: user.name })}}</h1>

          <section class="page__section">
            <loan-covid-collapsible-section />
          </section>

          <section class="page__section">
            <release-info-box :user="user" />
          </section>

          <section class="page__section" v-if="!hasCompletedRegistration">
            <b-jumbotron bg-variant="light"
              header="Inscription"
              :lead="$t('lead_text')">
              <b-button to="/register">Compléter l'inscription</b-button>
            </b-jumbotron>
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
                <tutorial-block title="Réservez un véhicule"
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

            <dashboard-resources-list :user="user" />
          </b-card>
        </b-col>
      </b-row>
    </b-container>

    <main-faq />

    <partners-section />
  </layout-page>
</template>

<i18n>
fr:
  'Bienvenue, {name}': Bienvenue, {name}
  lead_text: |
    Vous y êtes presque. Il ne vous manque que quelques étapes, pour prendre la route!
en:
  'Bienvenue, {name}': Welcome, {name}
</i18n>

<script>
import Authenticated from '@/mixins/Authenticated';

import DashboardBalance from '@/components/Dashboard/Balance.vue';
import DashboardLoanHistory from '@/components/Dashboard/LoanHistory.vue';
import DashboardResourcesList from '@/components/Dashboard/ResourcesList.vue';
import LoanCovidCollapsibleSection from '@/components/Loan/CovidCollapsibleSection.vue';
import LoanInfoBox from '@/components/Loan/InfoBox.vue';
import LoanableInfoBox from '@/components/Loanable/InfoBox.vue';
import MainFaq from '@/components/Misc/MainFaq.vue';
import ReleaseInfoBox from '@/components/Dashboard/ReleaseInfoBox.vue';
import TutorialBlock from '@/components/Dashboard/TutorialBlock.vue';
import PartnersSection from '@/components/Misc/PartnersSection.vue';

export default {
  name: 'Dashboard',
  mixins: [Authenticated],
  components: {
    DashboardBalance,
    DashboardLoanHistory,
    DashboardResourcesList,
    LoanCovidCollapsibleSection,
    LoanInfoBox,
    LoanableInfoBox,
    MainFaq,
    PartnersSection,
    ReleaseInfoBox,
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
          return this.user.owner && this.user.loanables && this.user.loanables.length === 0;
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
    &__main {
      padding-top: 45px;
      padding-bottom: 45px;
    }

    h2 {
      margin-bottom: 25px;
    }
  }

  .page__content {
    > h1 {
      margin-bottom: 60px;
    }

    .main-faq {
      padding-top: 65px;
      padding-bottom: 65px;
      margin-bottom: 0;
    }

    .partners-section {
      margin-top: 0;
      margin-bottom: 80px;
    }
  }

  &__vehicles {
    .loanable-info-box {
      margin-bottom: 20px;
    }
  }
}
</style>
