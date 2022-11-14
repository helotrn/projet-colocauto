<template>
  <layout-page name="dashboard" wide>
    <b-container>
      <b-row class="page__section page__section__main">
        <b-col class="page__content" xl="9" lg="8" md="7">
          <!-- main header -->
          <h1>{{ $t("welcome_text", { name: user.name }) }}</h1>

          <h3 v-if="hasCommunity" class="dashboard-h3">
            {{
              $t("welcome_description", {
                approvedUserCount: totalApprovedUsers,
                community: mainCommunity.name,
              })
            }}
          </h3>

          <!-- button to search for vehicule -->
          <section class="page__section" v-if="canLoanVehicle">
            <b-button pill to="/search/map" class="search_button">
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
                  to="/search/map"
                  bg-image="/img-vehicules.png"
                  variant="light"
                />
              </div>
            </div>
          </section>
          <!---->
          <div class="page__section position-relative">
            <div class="loans-container" :class="{ loading: loading && !loansLoaded }">
              <!-- contested loans container -->

              <transition name="fade">
                <section class="page__section" v-if="loans.contested && loans.contested.length > 0">
                  <h2>Emprunts avec contestation</h2>
                  <p class="dashboard__instructions">
                    Un membre de l'équipe LocoMotion contactera les participant-e-s et ajustera les
                    données.
                  </p>
                  <transition-group name="dashboard-list">
                    <div
                      class="dashboard-list-item dashboard__ongoing-loans"
                      v-for="loan in loans.contested"
                      :key="loan.id"
                    >
                      <loan-info-box
                        :loan="loan"
                        :user="user"
                        :buttons="['view']"
                        variant="warning"
                      />
                    </div>
                  </transition-group>
                </section>
              </transition>
              <!---->
              <!-- need approval loans container (user is owner)-->
              <transition name="fade">
                <section
                  class="page__section"
                  v-if="loans.need_approval && loans.need_approval.length > 0"
                >
                  <h2>Nouvelles demandes d'emprunt</h2>
                  <p class="dashboard__instructions">
                    Ces personnes devraient entrer en contact avec vous sous peu.
                  </p>
                  <transition-group name="dashboard-list">
                    <div
                      class="dashboard-list-item dashboard__waiting-loans"
                      v-for="loan in loans.need_approval"
                      :key="loan.id"
                    >
                      <loan-info-box :loan="loan" :user="user" />
                    </div>
                  </transition-group>
                </section>
              </transition>
              <!---->
              <!-- ongoing loans container -->
              <transition name="fade">
                <section class="page__section" v-if="loans.started && loans.started.length > 0">
                  <h2 class="dashboard--margin-bottom">Emprunts en cours</h2>
                  <transition-group name="dashboard-list">
                    <div
                      class="dashboard-list-item dashboard__ongoing-loans"
                      v-for="loan in loans.started"
                      :key="loan.id"
                    >
                      <loan-info-box :loan="loan" :user="user" :buttons="['view']" />
                    </div>
                  </transition-group>
                </section>
              </transition>
              <!---->
              <!-- awaiting loans container (user is borrower)-->
              <transition name="fade">
                <section class="page__section" v-if="loans.waiting && loans.waiting.length > 0">
                  <h2>Demandes en attente d'approbation</h2>
                  <p class="dashboard__instructions">
                    La demande est envoyée! Maintenant contactez la personne propriétaire pour
                    valider votre demande.
                  </p>
                  <transition-group name="dashboard-list">
                    <div
                      class="dashboard-list-item dashboard__waiting-loans"
                      v-for="loan in loans.waiting"
                      :key="loan.id"
                    >
                      <loan-info-box :loan="loan" :user="user" :buttons="['view', 'cancel']" />
                    </div>
                  </transition-group>
                </section>
              </transition>
              <!---->
              <!-- upcoming loans container -->
              <transition name="fade">
                <section class="page__section" v-if="loans.future && loans.future.length > 0">
                  <h2>Emprunts à venir approuvés</h2>
                  <p class="dashboard__instructions">
                    Assurez-vous de démarrer l'emprunt en ligne au moment de prendre possession du
                    véhicule!
                  </p>
                  <transition-group name="dashboard-list">
                    <div
                      class="dashboard-list-item dashboard__upcoming-loans"
                      v-for="loan in loans.future"
                      :key="loan.id"
                    >
                      <loan-info-box
                        mode="upcoming"
                        :loan="loan"
                        :user="user"
                        :buttons="['view', 'cancel']"
                      />
                    </div>
                  </transition-group>
                </section>
              </transition>
            </div>
            <layout-loading class="section-loading-indicator" v-if="loading && !loansLoaded" />
          </div>
          <!---->
          <!-- loanables container -->

          <section class="page__section position-relative" v-if="user.owner">
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

            <div class="dashboard__vehicles" :class="{ loading: loading && !loanablesLoaded }">
              <transition name="fade">
                <div v-if="loanables && loanables.length > 0">
                  <transition-group name="dashboard-list">
                    <loanable-info-box
                      class="dashboard-list-item"
                      v-for="loanable in loanables"
                      :key="loanable.id"
                      v-bind="loanable"
                      @disabled="hideLoanable"
                    />
                  </transition-group>
                  <div class="text-right" v-if="hasMoreLoanables">
                    <b-button variant="outline-primary" to="/profile/loanables">
                      Tous mes véhicules
                    </b-button>
                  </div>
                </div>
              </transition>
              <div v-if="!loanables || loanables.length == 0">
                Aucun véhicule.<br />
                Ajoutez-en un
                <router-link to="/profile/loanables/new">ici</router-link>.
              </div>
            </div>
            <layout-loading class="section-loading-indicator" v-if="loading && !loanablesLoaded" />
          </section>
          <!---->
        </b-col>

        <b-col tag="aside" class="page__sidebar" xl="3" lg="4" md="5">
          <!-- sidebar -->
          <b-card>
            <div v-if="hasCompletedRegistration">
              <dashboard-balance :user="user" />

              <hr />
            </div>

            <div v-if="hasCompletedRegistration">
              <layout-loading v-if="loading && !loansLoaded"></layout-loading>
              <dashboard-loan-history
                v-else
                :past-loans="loans.completed.slice(0, 3)"
                :upcoming-loans="loans.future.slice(0, 3)"
                :ongoing-loans="loans.started.slice(0, 3)"
                :waiting-loans="loans.waiting.slice(0, 3)"
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

const sendRectMap = new Map();

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

    if (this.isGlobalAdmin) {
      this.$router.replace("/admin");
    }

    if (!this.hasCompletedRegistration) {
      // Skip to 2 here since we already have an email (logged in)
      this.$router.replace("/register/2");
    }
  },
  mounted() {
    this.$store.dispatch("dashboard/reload", this.user.owner);
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
    loans() {
      return this.$store.state.dashboard.loans ?? {};
    },
    loanables() {
      return this.$store.state.dashboard.loanables ?? [];
    },
    loansLoaded() {
      return this.$store.state.dashboard.loansLoaded;
    },
    loanablesLoaded() {
      return this.$store.state.dashboard.loanablesLoaded;
    },
    hasMoreLoanables() {
      return this.$store.state.dashboard.hasMoreLoanables;
    },
    loading() {
      return this.$store.state.dashboard.loadRequests > 0;
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
          return !this.hasSubmittedProofOfResidency;
        default:
          return false;
      }
    },
    isBorrower(loan) {
      return this.user.id === loan.borrower.user.id;
    },
    async hideLoanable(id) {
      this.$store.commit(
        "dashboard/setLoanables",
        this.loanables.filter((l) => l.id !== id)
      );
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

    .no-communities-jumbotron {
      .btn {
        margin-left: 0;
      }
    }
  }

  .fade-enter-active,
  .fade-leave-active {
    transition: opacity 0.8s;
  }

  .fade-enter,
  .fade-leave-to {
    opacity: 0;
  }

  .dashboard-list-item {
    transition: all 0.8s;
  }

  .dashboard-list-leave-to,
  .dashboard-list-enter {
    opacity: 0;
  }
  .dashboard-list-leave-active {
    position: absolute;
    width: 100%;
  }

  .section-loading-indicator {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
  }
  .loans-container.loading,
  .dashboard__vehicles.loading {
    opacity: 0.5;
    pointer-events: none;
    min-height: 10rem;
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

  .dashboard-h3 {
    margin-bottom: 1rem;
    line-height: $h3-line-height;
    font-size: $h3-font-size;

    @include media-breakpoint-down(md) {
      line-height: $h4-line-height;
      font-size: $h4-font-size;
    }
  }

  .search_button {
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
