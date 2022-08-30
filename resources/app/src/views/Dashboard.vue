<template>
  <layout-page name="dashboard" wide>
    <b-container>
      <b-row class="page__section page__section__main">
        <b-col class="page__content" xl="9" lg="8" md="7">
          <!-- main header -->
          <h1>{{ $t("welcome_text", { name: user.name }) }}</h1>

          <h3 v-if="hasCommunity">
            {{
              $t("welcome_description", {
                approvedUserCount: totalApprovedUsers,
                community: mainCommunity.name,
              })
            }}
          </h3>

          <!-- button to search for vehicule -->
          <section class="page__section" v-if="canLoanVehicle">
            <b-button pill to="/community/map">
              <div class="dashboard--justify-text">
                <svg-magnifying-glass />
                Rechercher un véhicule
              </div>
            </b-button>
          </section>

          <!-- profile pending container -->
          <section class="page__section" v-if="waitingForProfileApproval && hasCommunity">
            <b-jumbotron
              bg-variant="light"
              header="Votre profil est en attente de validation."
              lead="LocoMotion s'assure que vos voisin-e-s soient bien... vos voisin-e-s! C'est pourquoi un membre de notre équipe va vérifier votre preuve de résidence et valider votre compte. Vous recevrez un courriel de confirmation et aurez alors accès à toutes les fonctionnalités de LocoMotion!"
            >
            </b-jumbotron>
          </section>

          <section class="page__section" v-if="!hasCommunity">
            <b-jumbotron
              bg-variant="light"
              class="no-communities-jumbotron"
              header="LocoMotion n'existe pas encore dans votre quartier"
              lead="Mais on y travaille! En attendant, devenez acteur de votre quartier et aidez LocoMotion à améliorer votre mobilité et celle de vos voisin-e-s."
            >
              <b-button variant="primary" href="https://bit.ly/locoquartier" target="_blank"
                >En savoir plus</b-button
              >
            </b-jumbotron>
          </section>

          <section class="page__section" v-if="hasTutorials">
            <h2 class="dashboard--margin-bottom">Pour commencer</h2>

            <div class="page__section__tutorials">
              <div v-if="hasTutorial('upload-proof-of-residency')">
                <tutorial-block
                  title="Veuillez fournir une preuve de résidence"
                  to="/profile/communities"
                  bg-image="/img-tetes.png"
                  variant="light"
                />
              </div>

              <div v-if="hasTutorial('fill-your-driving-profile')">
                <tutorial-block
                  title="Remplissez votre dossier de conduite"
                  to="/profile/borrower"
                  bg-image="/img-voiture.png"
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

              <div v-if="hasTutorial('find-vehicle')">
                <tutorial-block
                  title="Empruntez un véhicule"
                  to="/community/list"
                  bg-image="/img-vehicules.png"
                  variant="light"
                />
              </div>
            </div>
          </section>
          <!---->
          <layout-loading v-if="!allLoansLoaded" />
          <template v-else>
            <!-- awaiting loans container -->
            <section class="page__section" v-if="hasWaitingLoans">
              <h2 class="dashboard--margin-bottom">Nouvelles demandes d'emprunt</h2>

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
            <!---->
            <!-- ongoing loans container -->
            <section class="page__section" v-if="hasOngoingLoans">
              <h2 class="dashboard--margin-bottom">Emprunts en cours</h2>

              <div class="dashboard__ongoing-loans" v-for="loan in ongoingLoans" :key="loan.id">
                <loan-info-box :loan="loan" :user="user" :buttons="['view']" />
              </div>
            </section>
            <!---->
            <!-- upcoming loans container -->
            <section class="page__section" v-if="hasUpcomingLoans">
              <h2 class="dashboard--margin-bottom">Emprunts à venir</h2>

              <div class="dashboard__upcoming-loans" v-for="loan in upcomingLoans" :key="loan.id">
                <loan-info-box
                  mode="upcoming"
                  :loan="loan"
                  :user="user"
                  :buttons="['view', 'cancel']"
                />
              </div>
            </section>
            <!---->
            <!-- loanables container -->
            <section class="page__section" v-if="user.owner">
              <b-row>
                <b-col>
                  <h2 class="dashboard--margin-bottom">Mes véhicules</h2>
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
                  Ajoutez-en un
                  <router-link to="/profile/loanables/new">ici</router-link>.
                </div>
              </div>
            </section>
            <!---->
          </template>
        </b-col>

        <b-col tag="aside" class="page__sidebar" xl="3" lg="4" md="5">
          <!-- sidebar -->
          <b-card>
            <div v-if="hasCompletedRegistration">
              <dashboard-balance :user="user" />

              <hr />
            </div>

            <div v-if="hasCompletedRegistration">
              <layout-loading v-if="!allLoansLoaded"></layout-loading>
              <dashboard-loan-history
                v-else
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
          <!---->
        </b-col>
      </b-row>
    </b-container>

    <main-faq />

    <partners-section />
  </layout-page>
</template>

<script>
import locales from "@/locales";

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
    totalApprovedUsers() {
      if (this.hasCommunity) {
        return this.user.communities[0].approved_users_count;
      } else {
        return 0;
      }
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
        this.hasTutorial("find-vehicle") ||
        this.hasTutorial("fill-your-driving-profile") ||
        this.hasTutorial("upload-proof-of-residency")
      );
    },
  },
  methods: {
    hasTutorial(name) {
      switch (name) {
        case "add-vehicle":
          return this.user.owner && this.user.loanables && this.user.loanables.length === 0;
        case "find-vehicle":
          return this.canLoanVehicle;
        case "fill-your-driving-profile":
          return this.hasCommunity && (!this.user.borrower || !this.user.borrower.is_complete);
        case "upload-proof-of-residency":
          return this.hasNotSubmittedProofOfResidency;
        default:
          return false;
      }
    },
    isBorrower(loan) {
      return this.user.id === loan.borrower.user.id;
    },
  },
  i18n: {
    messages: {
      fr: {
        ...locales.fr.views.dashboard,
      },
      en: {
        ...locales.en.views.dashboard,
      },
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

    h2 {
      margin-bottom: 25px;
    }
    .no-communities-jumbotron {
      .btn {
        margin-left: 0;
      }
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

  h1 {
    font-weight: 700;

    @include media-breakpoint-down(md) {
      line-height: $h2-line-height;
      font-size: $h2-font-size;
    }
  }

  h2 {
    font-weight: 700;

    @include media-breakpoint-down(md) {
      line-height: $h3-line-height;
      font-size: $h3-font-size;
    }
  }

  h3 {
    margin-bottom: 25px;

    @include media-breakpoint-down(md) {
      line-height: $h4-line-height;
      font-size: $h4-font-size;
    }
  }

  .btn-secondary {
    background: #fff;
    color: #7a7a7a;
    border: 1px solid #e5e5e5;
    padding: 16px, 16px, 16px, 45px;
    margin: 0;
    width: 300px;

    &:hover {
      background: #fff;
      color: #7a7a7a;
    }
  }
}

.dashboard--margin-top {
  margin-top: 25px;
}

.dashboard--margin-bottom {
  margin-bottom: 25px;
}

.dashboard--justify-text {
  text-align: justify;
}
</style>
