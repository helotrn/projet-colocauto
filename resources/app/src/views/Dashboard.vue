<template>
  <layout-page name="dashboard" wide>
    <b-container>
      <b-row class="page__section page__section__main">
        <b-col class="page__content" xl="9" lg="8" md="7">
          <h1>{{ $t("welcome_text", { name: user.name }) }}</h1>
          <section class="page__section">
            <release-info-box />
          </section>

          <section
            class="page__section"
            v-if="hasCommunity && !hasProfileApproved && !hasNotSubmittedProofOfResidency"
          >
            <b-jumbotron
              bg-variant="light"
              header="Votre profil est en attente de validation."
              lead="LocoMotion s'assure que vos voisins soient bien... vos voisins! C'est pourquoi un membre de notre équipe va vérifier votre preuve de résidence et valider votre compte. Vous recevrez un courriel de confirmation et aurez alors accès à toutes les fonctionnalités de LocoMotion! "
            >
            </b-jumbotron>
          </section>

          <section class="page__section" v-if="!hasCommunity">
            <b-jumbotron
              bg-variant="light"
              class="no-communities-jumbotron"
              header="LocoMotion n'est pas encore ouvert dans votre quartier."
              lead="LocoMotion travaille fort pour être disponible dans de nouveaux quartiers. Nous aimerions en savoir plus sur ce qui vous serait le plus utile. Écrivez-nous"
            >
              <b-button variant="primary" href="mailto:info@locomotion.app"
                >Écrire à LocoMotion</b-button
              >
            </b-jumbotron>
          </section>

          <section class="page__section" v-if="hasTutorials">
            <h2>Pour commencer</h2>

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

          <section class="page__section" v-if="hasWaitingLoans">
            <h2>Nouvelles demandes d'emprunt</h2>

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
            <h2>Emprunts en cours</h2>

            <div class="dashboard__ongoing-loans" v-for="loan in ongoingLoans" :key="loan.id">
              <loan-info-box :loan="loan" :user="user" :buttons="['view']" />
            </div>
          </section>

          <section class="page__section" v-if="hasUpcomingLoans">
            <h2>Emprunts à venir</h2>

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
          return !this.user.borrower || !this.user.borrower.is_complete;
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
    .no-communities-jumbotron {
      .btn {
        margin-left: 0;
      }
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
