<template>
  <layout-page name="dashboard" wide>
    <b-container>
      <b-row class="page__section page__section__main">
        <b-col class="page__content" xl="9" lg="8" md="7">

          <!-- button to search for vehicule -->
          <section class="page__section d-flex flex-column" v-if="canLoanVehicle">
            <b-row>
              <b-col md="6">
                <b-button class="mb-2 py-2 w-100" variant="primary" to="/search/calendar">Réserver un véhicule</b-button>
              </b-col>
              <b-col md="6">
                <b-button class="mb-2 py-2 w-100" variant="primary" to="/wallet/expenses/new">Déclarer une dépense</b-button>
              </b-col>
            </b-row>
          </section>

          <conditions-updated-toast />

          <!-- profile pending container -->
          <section class="page__section" v-if="waitingForProfileApproval && hasCommunity">
            <b-jumbotron
              bg-variant="light"
              header="Votre profil est en attente de validation."
              lead="Coloc'Auto s'assure que vos voisin-e-s soient bien... vos voisin-e-s! C'est pourquoi un membre de notre équipe va vérifier votre preuve de résidence et valider votre compte. Vous recevrez un courriel de confirmation et aurez alors accès à toutes les fonctionnalités de Coloc'Auto!"
            >
            </b-jumbotron>
          </section>

          <section class="page__section" v-if="!hasCommunity">
            <div class="box centered">
              <svg-discussion width="135px" class="p-2"/>
              <p>Pour utiliser Colocauto, vous devez faire partie d'une communauté.
              C'est au sein des communautés que les conducteurs partagent l'utilisation
              de véhicules.</p>
              <b-button
                variant="primary"
                to="/communities/new"
              >
                Créer une communauté
              </b-button>
            </div>
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
                    Un membre de l'équipe Coloc'Auto contactera les participant-e-s et ajustera les
                    données.
                  </p>
                  <transition-group name="dashboard-list" tag="div" class="swiping-list">
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
                  <transition-group name="dashboard-list" tag="div" class="swiping-list">
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
                <section class="page__section" v-if="startedOrFutureLoans && startedOrFutureLoans.length > 0">
                  <h2 class="dashboard--margin-bottom">Emprunts en cours</h2>
                  <transition-group name="dashboard-list" tag="div" class="swiping-list">
                    <div
                      class="dashboard-list-item dashboard__ongoing-future-loans"
                      v-for="loan in startedOrFutureLoans"
                      :key="loan.id"
                    >
                      <loan-info-box :loan="loan" :user="user" :buttons="['modify','cancel']" @edit="showLoanModal(loan)" />
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
                  <transition-group name="dashboard-list" tag="div" class="swiping-list">
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
            </div>
            <layout-loading class="section-loading-indicator" v-if="loading && !loansLoaded" />
          </div>
          <!---->

          <!-- Expenses container -->
          <section class="page__section position-relative d-md-none" v-if="hasCommunity">
            <b-row>
              <b-col>
                <h2 class="dashboard--margin-bottom">Les comptes</h2>
              </b-col>
            </b-row>
            <b-row>
              <b-col>
                <div class="dashboard__balance" :class="{ loading: loading && !balanceLoaded }">
                  <transition name="fade">
                    <div v-if="balance && balance.users && balance.users.length > 0">
                      <users-balance :users="balance.users"/>
                    </div>
                  </transition>
                </div>
              </b-col>
            </b-row>
            <b-row>
              <b-col class="d-flex flex-column">
                <b-button class="mb-2 py-2" variant="primary" to="/wallet">Voir le portefeuille</b-button>
                <info-link-block title="Comment sont calculés les coûts ?" to="https://www.colocauto.org/tarification">
                   <svg-magnifying-glass-euro width="100px" class="p-2"/>
                </info-link-block>
              </b-col>
            </b-row>
          </section>

          <!-- Calendar container -->
          <section class="page__section position-relative" v-if="carsList && carsList.length > 0">
            <b-row>
              <b-col>
                <h2 class="dashboard--margin-bottom">Réserver un véhicule</h2>
              </b-col>
            </b-row>
            <b-row>
              <b-col>
                <b-tabs pills card>
                  <b-tab
                    v-for="loanable in carsList"
                    :title="loanable.name"
                    :key="loanable.id"
                    title-item-class="mr-2"
                    lazy
                  >
                    <b-card-text>
                      <loans-calendar
                        :loanable="loanable"
                        variant="small"
                        :defaultView="defaultView"
                        @view-change="defaultView = $event"
                      ></loans-calendar>
                      <loanable-calendar-legend />
                    </b-card-text>
                  </b-tab>
                </b-tabs>
              </b-col>
            </b-row>
            <b-row>
              <b-col md="6">
                <b-button v-if="canLoanVehicle" class="mb-2 py-2 w-100" variant="primary" to="/search/calendar">Voir tout l'agenda</b-button>
              </b-col>
              <b-col md="6">
                <b-button class="mb-2 py-2 w-100" variant="primary" to="/profile/loans">Historique d'emprunts</b-button>
              </b-col>
            </b-row>
          </section>

          <loan-modal-box v-model="showModal" :loan="currentLoan" @hidden="refresh"/>

          <!-- loanables container -->
          <section class="page__section position-relative">
            <b-row v-if="user.owner">
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
                    <h3 class="dashboard--margin-bottom" key="title">{{ totalLoanables }} véhicules</h3>
                    <b-row key="loanables">
                      <b-col lg="6" v-for="loanable in loanables" :key="loanable.id">
                        <loanable-info-box
                          class="dashboard-list-item"
                          v-bind="loanable"
                          @disabled="hideLoanable"
                        />
                      </b-col>
                    </b-row>
                  </transition-group>
                  <div class="text-right" v-if="hasMoreLoanables">
                    <b-button variant="outline-primary" to="/search/list">
                      Voir tous les véhicules disponibles
                    </b-button>
                  </div>
                </div>
              </transition>
            </div>
            <layout-loading class="section-loading-indicator" v-if="loading && !loanablesLoaded" />
          </section>
          <!---->

          <!-- members container -->
          <section class="page__section position-relative" v-if="hasCommunity">
            <div class="dashboard__members" :class="{ loading: loading && !membersLoaded }">
              <transition name="fade">
                <div v-if="members && members.length > 0">
                  <transition-group name="dashboard-list">
                    <h3 class="dashboard--margin-bottom" key="title">{{ totalMembers }} membres</h3>
                    <b-row key="members">
                      <b-col lg="6" v-for="member in members" :key="member.id">
                        <user-card
                          :user="member"
                          :is-admin="false"
                          :community-id="communityId"
                        />
                      </b-col>
                    </b-row>
                    <b-row key="moreMembers">
                      <b-col class="text-right">
                        <b-button
                          v-if="hasMoreMembers"
                          class="mb-4 py-2"
                          variant="outline-primary"
                          to="/community"
                        >Voir tous les membres</b-button>
                      </b-col>
                    </b-row>
                  </transition-group>
                </div>
              </transition>
            </div>
          </section>
        </b-col>

        <b-col tag="aside" class="page__sidebar" xl="3" lg="4" md="5">
          <!-- sidebar -->
            <div v-if="false && hasCompletedRegistration">
              <dashboard-balance :user="user" />

              <hr />
            </div>

            <div class="mb-4 d-none d-md-block" v-if="hasCommunity">
              <b-card title="Les comptes" title-tag="h2">
                <div class="dashboard__balance" :class="{ loading: loading && !balanceLoaded }">
                  <transition name="fade">
                    <div v-if="balance && balance.users && balance.users.length > 0">
                      <users-balance :users="balance.users"/>
                    </div>
                  </transition>
                </div>
              </b-card>
              <b-button class="my-4 py-2 w-100" variant="primary" to="/wallet">Voir le portefeuille</b-button>
              <info-link-block title="Comment sont calculés les coûts ?" to="https://www.colocauto.org/tarification">
                 <svg-magnifying-glass-euro width="100px" class="p-2" style="flex-shrink: 0"/>
              </info-link-block>
            </div>

            <div v-if="false && hasCompletedRegistration">
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

            <h2 class="dashboard--margin-bottom">Ressources</h2>
            <info-link-block title="Faire un don" to="https://www.colocauto.org/don">
              <svg-salut-coeur width="100px" class="p-2"/>
            </info-link-block>
            <info-link-block title="Foire aux questions" to="https://www.colocauto.org/faq">
              <svg-question width="100px" class="p-2"/>
            </info-link-block>
            <info-link-block title="Assurance" to="https://www.colocauto.org/assurance">
              <svg-pen-paper width="100px" class="p-2"/>
            </info-link-block>
            <info-link-block title="Contactez-nous" to="mailto:soutien@colocauto.org">
              <svg-waving class="m-2 white-round"/>
            </info-link-block>
          <!---->
        </b-col>
      </b-row>
    </b-container>

  </layout-page>
</template>

<script>
import locales from "@/locales";

import Authenticated from "@/mixins/Authenticated";
import UserMixin from "@/mixins/UserMixin";

import DashboardBalance from "@/components/Dashboard/Balance.vue";
import DashboardLoanHistory from "@/components/Dashboard/LoanHistory.vue";
import LoanInfoBox from "@/components/Loan/InfoBox.vue";
import LoanModalBox from "@/components/Loan/ModalBox.vue";
import LoanableInfoBox from "@/components/Loanable/InfoBox.vue";
import ReleaseInfoBox from "@/components/Dashboard/ReleaseInfoBox.vue";
import TutorialBlock from "@/components/Dashboard/TutorialBlock.vue";
import UserCard from "@/components/User/UserCard.vue";
import InfoLinkBlock from "@/components/Dashboard/InfoLinkBlock.vue";
import UsersBalance from "@/components/Balance/UsersBalance.vue";
import LoansCalendar from "@/components/Loanable/LoansCalendar.vue";
import LoanableCalendarLegend from "@/components/Loanable/CalendarLegend.vue";
import ConditionsUpdatedToast from "@/views/ConditionsUpdatedToast.vue";

import MagnifyingGlass from "@/assets/svg/magnifying-glass.svg";
import SalutCoeur from "@/assets/svg/salut-coeur.svg";
import Question from "@/assets/svg/question.svg";
import PenPaper from "@/assets/svg/pen-paper.svg";
import Waving from "@/assets/svg/waving.svg";
import MagnifyingGlassEuro from "@/assets/svg/magnifying-glass-euro.svg";
import Discussion from "@/assets/svg/discussion.svg";

const sendRectMap = new Map();

export default {
  name: "Dashboard",
  mixins: [Authenticated, UserMixin],
  components: {
    DashboardBalance,
    DashboardLoanHistory,
    LoanInfoBox,
    LoanModalBox,
    LoanableInfoBox,
    ReleaseInfoBox,
    TutorialBlock,
    "svg-magnifying-glass": MagnifyingGlass,
    "svg-magnifying-glass-euro": MagnifyingGlassEuro,
    "svg-salut-coeur": SalutCoeur,
    "svg-question": Question,
    "svg-pen-paper": PenPaper,
    "svg-waving": Waving,
    "svg-discussion": Discussion,
    UserCard,
    InfoLinkBlock,
    UsersBalance,
    LoansCalendar,
    LoanableCalendarLegend,
    ConditionsUpdatedToast,
  },
  beforeMount() {
    if (!this.isLoggedIn) {
      this.skipToLogin();
    }

    if (this.isGlobalAdmin || this.isCommunityAdmin) {
      this.$router.replace("/admin");
    }

    if (!this.hasCompletedRegistration) {
      // Skip to 2 here since we already have an email (logged in)
      this.$router.replace("/register/2");
    }
  },
  mounted() {
    this.$store.dispatch("dashboard/reload", this.user);
  },
  data: () => ({
    defaultView: 'week',
    showModal: false,
  }),
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
    communityId(){
      if (this.hasCommunity) {
        return this.user.communities[0].id
      } else {
        0;
      }
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
      if(!this.$store.state.dashboard.loanables) return [];
      else return this.$store.state.dashboard.loanables
        .filter(l => l.community?.id == this.currentCommunity);
    },
    otherCommunitiesLoanables() {
      if(!this.$store.state.dashboard.loanables) return [];
      else return this.$store.state.dashboard.loanables
        .filter(l => l.community?.id !== this.currentCommunity);
    },
    members() {
      return this.$store.state.dashboard.members ?? [];
    },
    balance() {
      return this.$store.state.dashboard.balance ?? [];
    },
    totalMembers() {
      return this.$store.state.dashboard.totalMembers;
    },
    hasMoreMembers() {
      return this.$store.state.dashboard.hasMoreMembers;
    },
    carsList(){
      return this.$store.state.dashboard.loanables
        .filter(car => car.community?.id == this.currentCommunity)
        .sort((c1,c2) => {
          // cars owned by current user should appear first
          if(c1.owner?.user.id == this.user.id) {
            if(c2.owner?.user.id == this.user.id) {
              return c1.id - c2.id
            } else {
              return -1
            }
          } else {
            if(c2.owner?.user.id == this.user.id) {
              return 1
            } else {
              return c1.id - c2.id
            }
          }
        });
    },
    loansLoaded() {
      return this.$store.state.dashboard.loansLoaded;
    },
    loanablesLoaded() {
      return this.$store.state.dashboard.loanablesLoaded;
    },
    membersLoaded() {
      return this.$store.state.dashboard.membersLoaded;
    },
    totalLoanables() {
      return this.$store.state.dashboard.totalLoanables - this.otherCommunitiesLoanables.length;
    },
    hasMoreLoanables() {
      return this.$store.state.dashboard.hasMoreLoanables || this.otherCommunitiesLoanables.length;
    },
    balanceLoaded() {
      return this.$store.state.dashboard.balanceLoaded;
    },
    loading() {
      return this.$store.state.dashboard.loadRequests > 0;
    },
    startedOrFutureLoans() {
      return this.$store.state.dashboard.loans.started.concat(this.$store.state.dashboard.loans.future).toSorted((a,b) => {

        // display current community loans first
        if( a.loanable.community ){
          if( b.loanable.community ){
            if( a.loanable.community.id == this.currentCommunity ){
              if( b.loanable.community.id == this.currentCommunity ) {
                return b.departure_at > a.departure_at ? -1 : 1
              } else {
                return -1
              }
            } else {
              return 1
            }
          } else {
            if( a.loanable.community.id == this.currentCommunity ) {
              return b.departure_at > a.departure_at ? -1 : 1
            } else {
              return 1
            }
          }
        } else {
          if( b.loanable.community ){
            if( b.loanable.community.id == this.currentCommunity ) {
              return b.departure_at > a.departure_at ? -1 : 1
            } else {
              return -1
            }
          } else {
            return b.departure_at > a.departure_at ? -1 : 1
          }
        }
      });
    },
    loansRoute() {
      return this.$router.options.routes.find(r => r.meta && r.meta.slug == 'loans')
    },
    currentLoan() {
      return this.$store.state.loans.item
    },
  },
  methods: {
    hasTutorial(name) {
      switch (name) {
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
    async showLoanModal(loan) {
      await this.$store.dispatch('loans/retrieveOne', {
        id: loan.id,
        params: this.loansRoute.meta.params,
      });
      this.showModal = true;
    },
    refresh() {
      this.$store.dispatch("dashboard/reload", this.user);
    },
    changeCommunity(communityId) {
      this.$store.dispatch("communities/setCurrent", { communityId })
      this.$store.dispatch("dashboard/loadBalance", { community: {id:communityId} })
      this.$store.dispatch("dashboard/loadMembers", { user: this.user })
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
    .btn-link {
      vertical-align: baseline;
      padding: 0;
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

  .tabs {
    .card-header {
      background-color: transparent;
      padding-left: 0.625rem;
      padding-right: 0.625rem;
    }

    .tab-pane.card-body {
      padding-left: 0;
      padding-right: 0;
      padding-top: 0;
    }

    .nav-pills .nav-link {
      color: $secondary;
      background-color: $white;
      border: solid 1px $white;
      border-radius: 10px;
    }
    .nav-pills .nav-link.active,
    .nav-pills .show > .nav-link {
      color: $primary;
      border-color: $primary;
      font-weight: bold;
    }
  }

  .text-right .btn {
    width: 100%;
    @include media-breakpoint-up(md) {
      width: auto;
    }
  }
}

.dashboard--margin-top {
  margin-top: 25px;
}

.dashboard--margin-bottom {
  margin-bottom: 11px;
}

.dashboard--justify-text {
  text-align: justify;
}

.swiping-list {
  display: flex;
  overflow-x: scroll;
  scroll-snap-type: x mandatory;
}

.swiping-list .dashboard-list-item {
  width: 67%;
  max-width: 250px;
  flex-shrink: 0;
  margin-right: 10px;
  scroll-snap-align: start;
  display: flex;
  flex-direction: column;

}
.swiping-list .dashboard-list-item:first-child:last-child {
  margin: auto;
}

.tabs {
  .card-header {
    padding-bottom: 0;
    .nav {
      flex-wrap: nowrap;
      overflow-y: scroll;
      padding-bottom: 0.75rem;
      .nav-item {
        flex-shrink: 0;
      }
    }
  }
}

/* force scrollbar display on safari chrome */
::-webkit-scrollbar {
    -webkit-appearance: none;
    width: 7px;
}
::-webkit-scrollbar-thumb {
    border-radius: 4px;
    background-color: rgba(0,0,0,.5);
    -webkit-box-shadow: 0 0 1px rgba(255,255,255,.5);
}

.white-round {
  background: #f9f9f9;
  border-radius: 100%;
  width: 82px;
  height: 82px;
}
</style>
