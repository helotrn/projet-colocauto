<template>
  <layout-page name="dashboard" wide>
    <b-container>
      <b-row class="page__section page__section__main">
        <b-col class="page__content" xl="9" lg="8" md="7">
          <p class="dashboard__title dashboard__title__main">{{ $t("welcome_text", { name: user.name }) }}</p>
          <p class="dashboard__subtitle">{{ $t("welcome_description", {userCount: totalUsers, community: communityName}) }}</p>

          <section class="page__section" v-if="!hasCompletedRegistration">
            <b-jumbotron bg-variant="light" header="Inscription" :lead="$t('lead_text')">
              <b-button to="/register">Compléter l'inscription</b-button>
            </b-jumbotron>
          </section>

          <section class="page__section" v-else>
            <b-button pill to="/community/map" class="btn-search-vehicule">
              <div class="btn-search-vehicule__text">
                <svg-magnifying-glass />
                Rechercher un véhicule
              </div>
            </b-button>
          </section>

          <section class="page__section" v-if="hasTutorials">
            <p class="dashboard__title">Pour commencer</p>

            <div class="page__section__tutorials">
              <div v-if="hasTutorial('discover-community')">
                <tutorial-block
                  :title="discoverCommunityTitle"
                  to="/community"
                  bg-image="/img-tetes.png"
                  variant="dark"
                />
              </div>

              <div v-if="hasTutorial('add-vehicle')">
                <tutorial-block
                  title="Inscrivez un véhicule"
                  to="/profile/loanables/new"
                  bg-image="/img-voiture.png"
                  variant="dark"
                />
              </div>
            </div>
          </section>

          <section class="page__section" v-if="hasWaitingLoans">
            <p class="dashboard__title">Nouvelles demandes d'emprunt</p>

            <div class="dashboard__waiting-loans" v-for="loan in waitingLoans" :key="loan.id">
              <loan-info-box
                v-if="isBorrower(loan)"
                :loan="loan"
                :user="user"
                :buttons="['view', 'cancel']"
              />
              <loan-info-box v-else :loan="loan" :user="user" />
            </div>
          </section>

          <section class="page__section" v-if="hasOngoingLoans">
            <p class="dashboard__title">Emprunts en cours</p>

            <div class="dashboard__ongoing-loans" v-for="loan in ongoingLoans" :key="loan.id">
              <loan-info-box :loan="loan" :user="user" :buttons="['view']" />
            </div>
          </section>

          <section class="page__section" v-if="hasUpcomingLoans">
            <p class="dashboard__title">Emprunts à venir</p>

            <div class="dashboard__upcoming-loans" v-for="loan in upcomingLoans" :key="loan.id">
              <loan-info-box
                mode="upcoming"
                :loan="loan"
                :user="user"
                :buttons="['view', 'cancel']"
              />
            </div>
          </section>

          <section class="page__section" v-if="user.owner">
            <b-row>
              <b-col>
                <p class="dashboard__title">Mes véhicules</p>
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
                  v-for="loanable in user.loanables"
                  :key="loanable.id"
                  v-bind="loanable"
                />
              </div>
              <div v-else>
                Aucun véhicule.<br />
                Ajoutez-en un <router-link to="/profile/loanables/new">ici</router-link>.
              </div>
            </div>
          </section>
        </b-col>

        <b-col tag="aside" class="page__sidebar" xl="3" lg="4" md="5">
          <b-card>
            <div v-if="hasCompletedRegistration">
              <dashboard-balance :user="user" />

              <hr />
            </div>

            <div v-if="hasCompletedRegistration">
              <dashboard-loan-history
                :past-loans="pastLoans.slice(0, 3)"
                :upcoming-loans="upcomingLoans.slice(0, 3)"
                :ongoing-loans="ongoingLoans.slice(0, 3)"
                :waiting-loans="waitingLoans.slice(0, 3)"
                :borrower="user.borrower"
              />

              <hr />
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
  welcome_text: Bienvenue {name},
  welcome_description: Vous êtes {userCount} voisin-e-s à {community}.
  lead_text: |
    Vous y êtes presque. Il ne vous manque que quelques étapes, pour prendre la route!
en:
  welcome_text: Welcome {name}!
</i18n>

<script>
import Authenticated from "@/mixins/Authenticated";
import UserMixin from "@/mixins/UserMixin";

import DashboardBalance from "@/components/Dashboard/Balance.vue";
import DashboardLoanHistory from "@/components/Dashboard/LoanHistory.vue";
import DashboardResourcesList from "@/components/Dashboard/ResourcesList.vue";
import LoanInfoBox from "@/components/Loan/InfoBox.vue";
import LoanableInfoBox from "@/components/Loanable/InfoBox.vue";
import MainFaq from "@/components/Misc/MainFaq.vue";
import ReleaseInfoBox from "@/components/Dashboard/ReleaseInfoBox.vue";
import TutorialBlock from "@/components/Dashboard/TutorialBlock.vue";
import PartnersSection from "@/components/Misc/PartnersSection.vue";

import MagnifyingGlass from "@/assets/svg/magnifying-glass.svg";

export default {
  name: "Dashboard",
  mixins: [Authenticated, UserMixin],
  components: {
    DashboardBalance,
    DashboardLoanHistory,
    DashboardResourcesList,
    LoanInfoBox,
    LoanableInfoBox,
    MainFaq,
    PartnersSection,
    ReleaseInfoBox,
    TutorialBlock,
    "svg-magnifying-glass": MagnifyingGlass,
  },
  beforeMount() {
    if (!this.isLoggedIn) {
      this.skipToLogin();
    }

    if (!this.user.name) {
      this.$router.replace("/register");
    }

    if (this.user.role === "admin") {
      this.$router.replace("/admin");
    }
  },
  computed: {
    communityName() {
      return this.user.communities[0].name;
    },
    totalUsers() {
      return this.$store.state.stats.data.users;
    },
    discoverCommunityTitle() {
      if (this.user && this.user.communities && this.user.communities[0].type === "borough") {
        return "Découvrez votre quartier";
      }

      return "Découvrez votre voisinage";
    },
    hasTutorials() {
      return (
        this.hasTutorial("add-vehicle") ||
        this.hasTutorial("discover-community")
      );
    },
  },
  methods: {
    hasTutorial(name) {
      switch (name) {
        case "add-vehicle":
          return this.user.owner && this.user.loanables && this.user.loanables.length === 0;
        case "discover-community":
          return (
            this.hasCommunity &&
            this.user.created_at >= this.$dayjs().subtract(2, "week").format("YYYY-MM-DD HH:mm:ss")
          );
        default:
          return false;
      }
    },
    isBorrower(loan) {
      return this.user.id === loan.borrower.user.id;
    },
  },
};
</script>

<style lang="scss">
@import "~bootstrap/scss/mixins/breakpoints";

.dashboard {
  .page__section {
    &__main {
      padding-top: 45px;
      padding-bottom: 45px;
    }
  }

  .page__content {
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

  &__title {
    line-height: $h3-line-height;
    font-size: $h3-font-size;
    font-weight: 700;
    margin-bottom: 25px;

    &__main {
      margin-bottom: 5px;
    }
  }

  &__subtitle {
    line-height: $h4-line-height;
    font-size: $h4-font-size;
  }

  @include media-breakpoint-up(lg) {
    &__title {
      line-height: $h2-line-height;
      font-size: $h2-font-size;
    }

    &__subtitle {
      line-height: $h3-line-height;
      font-size: $h3-font-size;
    }
  }
}
</style>
