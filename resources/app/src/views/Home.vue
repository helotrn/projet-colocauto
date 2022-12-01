<template>
  <layout-page name="home" wide>
    <section class="page__section page__section--top mb-0">
      <b-container>
        <h1 class="page__section__title">
          Communautés <br class="d-lg-none" />mobiles <br />et solidaires
        </h1>

        <div class="page__section__content">
          <b-button size="lg" variant="warning" to="register" v-if="!userRegistered"
            >S'inscrire</b-button
          >
          <b-button v-if="!userLoggedIn" size="lg" variant="outline-light" to="login">
            Se connecter
          </b-button>
          <b-button v-else size="lg" variant="outline-light" to="/app"> Tableau de bord </b-button>
        </div>
      </b-container>

      <svg-dot class="wave-dot d-lg-none" />
      <svg-dot-desktop class="wave-dot d-none d-lg-block" />
      <svg-wave class="wave-svg d-lg-none" />
      <svg-wave-desktop class="wave-svg d-none d-lg-block" />
    </section>

    <section class="page__section page__section--hero mb-0">
      <b-col>
        <div class="page__section__content">
          <svg-clouds class="d-lg-none" />
          <svg-clouds-desktop class="d-none d-lg-block" />
          <p>
            Partager avec ses <span class="no-break">voisin-e-s</span> des voitures, vélos et
            remorques à vélos de manière conviviale et sécuritaire.
          </p>
          <svg-city class="d-lg-none" />
          <svg-city-desktop class="d-none d-lg-block" />
        </div>
      </b-col>
    </section>

    <section class="page__section page__section--what-is-it">
      <b-container>
        <h2 class="page__section__title mb-4">C’est quoi<br />LocoMotion?</h2>

        <div class="page__section__content">
          <div class="page__section__illustration page__section__illustration--1">
            <svg-borrow />
          </div>

          <div class="page__section__illustration page__section__illustration--2">
            <svg-lend />
          </div>

          <div class="page__section__illustration page__section__illustration--3">
            <svg-tridem />
          </div>

          <div class="page__section__text page__section__text--1">
            <p>Empruntez une auto, un vélo, une remorque&mldr; à 5 minutes à pied</p>
          </div>

          <div class="page__section__text page__section__text--2">
            <p>
              Partagez votre auto avec vos
              <span class="no-break">voisin-e-s</span>
            </p>
          </div>

          <div class="page__section__text page__section__text--3">
            <p>Faites partie d’un projet collectif ambitieux</p>
          </div>
        </div>
      </b-container>
    </section>

    <section class="page__section page__section--why-locomotion">
      <b-container>
        <h2 class="page__section__title mb-4">4 raisons de choisir<br />LocoMotion</h2>

        <div class="page__section__content">
          <div class="page__section__illustration page__section__illustration--1">
            <svg-waving />
          </div>
          <div class="page__section__illustration page__section__illustration--2">
            <svg-driving />
          </div>
          <div class="page__section__illustration page__section__illustration--3">
            <svg-biking />
          </div>
          <div class="page__section__illustration page__section__illustration--4">
            <svg-smiling-heart />
          </div>
          <div class="page__section__text page__section__text--1">
            <p>
              C’est un projet <strong>par et pour votre voisinage</strong>, qui évolue avec vous
            </p>
          </div>

          <div class="page__section__text page__section__text--2">
            <p>Soyez protégé-e avec <strong>une assurance complète</strong>, pas compliquée</p>
          </div>

          <div class="page__section__text page__section__text--3">
            <p><strong>Économisez</strong> grâce à ce projet citoyen à but non-lucratif</p>
            <!-- <b-button size="lg" variant="outline-info"> Voir les prix </b-button> -->
          </div>

          <div class="page__section__text page__section__text--4">
            <p>
              Optez pour
              <strong>la seule solution locale</strong> d’autopartage entre voisin-e-s
            </p>
          </div>
        </div>
      </b-container>
    </section>

    <section class="page__section page__section--participants">
      <b-container fluid>
        <b-row>
          <b-col lg="6">
            <b-row class="page__section--participants__photos">
              <b-col col class="page__section--participants__photo">
                <img src="/user1.png" />
              </b-col>
              <b-col col class="page__section--participants__photo">
                <img src="/user2.png" />
              </b-col>
              <b-col col class="page__section--participants__photo">
                <img src="/user3.png" />
              </b-col>
            </b-row>
          </b-col>
          <b-col lg="6">
            <div class="page__section--participants__text">
              <h2 class="mb-4 page__section__title">
                Plus de {{ roundedStatsUsers }}
                <span class="no-break">participant-e-s!</span>
              </h2>

              <p class="mb-5">
                Aujourd’hui,
                <span class="no-break">{{
                  $tc("borough", boroughsCount, { count: boroughsCount })
                }}</span>
                et
                <span class="no-break">{{
                  $tc("neighborhood", neighborhoodsCount, {
                    count: neighborhoodsCount,
                  })
                }}</span
                >, soit plus de
                <span class="no-break">{{ stats.users }} participant•e•s</span>
                partageant
                <span class="no-break">{{ stats.loanables }}</span> véhicules expérimentent cette
                solution collective.
              </p>

              <div>
              </div>
            </div>
          </b-col>
        </b-row>
      </b-container>
    </section>

    <section class="page__section page__section--community mb-0">
      <b-container>
        <b-row align-v="center">
          <b-col lg="6" order-lg="2">
            <svg-community class="d-lg-none svg-community" />
            <svg-community-desktop class="d-none d-lg-block svg-community" />
          </b-col>
          <b-col lg="6">
            <h2 class="mt-4 mb-4 page__section__title">
              Rejoignez vos <span class="no-break">voisin-e-s</span>
            </h2>

            <p class="mb-5">
              Participez à la vie de quartier en vous déplaceant autrement. Des voisins et des
              voisines s'activent, prenez part au mouvement!
            </p>

            <b-button size="lg" variant="warning" to="register">S'inscrire</b-button>
          </b-col>
        </b-row>
      </b-container>
    </section>

    <main-faq />

    <partners-section />
  </layout-page>
</template>

<script>
import locales from "@/locales";

import MainFaq from "@/components/Misc/MainFaq.vue";
import PartnersSection from "@/components/Misc/PartnersSection.vue";

import DataRouteGuards from "@/mixins/DataRouteGuards";

import Borrow from "@/assets/svg/home-borrow.svg";
import CityDesktop from "@/assets/svg/home-city-desktop.svg";
import City from "@/assets/svg/home-city.svg";
import CloudsDesktop from "@/assets/svg/home-clouds-desktop.svg";
import Clouds from "@/assets/svg/home-clouds.svg";
import CommunityDesktop from "@/assets/svg/home-community-desktop.svg";
import Community from "@/assets/svg/home-community.svg";
import DotDesktop from "@/assets/svg/home-dot-desktop.svg";
import Dot from "@/assets/svg/home-dot.svg";
import Lend from "@/assets/svg/home-lend.svg";
import Tridem from "@/assets/svg/home-tridem.svg";
import WaveDesktop from "@/assets/svg/home-wave-desktop.svg";
import Wave from "@/assets/svg/home-wave.svg";
import UserMixin from "@/mixins/UserMixin";

import SmilingHeart from "@/assets/svg/smiling-heart.svg";
import Driving from "@/assets/svg/driving.svg";
import Biking from "@/assets/svg/biking.svg";
import Waving from "@/assets/svg/waving.svg";

export default {
  name: "Home",
  mixins: [DataRouteGuards, UserMixin],
  components: {
    MainFaq,
    PartnersSection,
    "svg-biking": Biking,
    "svg-borrow": Borrow,
    "svg-city-desktop": CityDesktop,
    "svg-city": City,
    "svg-clouds-desktop": CloudsDesktop,
    "svg-clouds": Clouds,
    "svg-community-desktop": CommunityDesktop,
    "svg-community": Community,
    "svg-dot-desktop": DotDesktop,
    "svg-dot": Dot,
    "svg-driving": Driving,
    "svg-lend": Lend,
    "svg-smiling-heart": SmilingHeart,

    "svg-tridem": Tridem,
    "svg-wave-desktop": WaveDesktop,
    "svg-wave": Wave,
    "svg-waving": Waving,
  },
  computed: {
    communitiesCount() {
      if (this.stats && this.stats.communities) {
        return this.stats.communities.length;
      }

      return 5;
    },
    neighborhoodsCount() {
      const neighborhoods = this?.stats?.communities?.reduce((acc, r) => {
        if (r.type && r.type === "neighborhood") {
          acc++;
        }
        return acc;
      }, 0);

      return neighborhoods;
    },
    boroughsCount() {
      const boroughs = this?.stats?.communities?.reduce((acc, r) => {
        if (r.type && r.type === "borough") {
          acc++;
        }
        return acc;
      }, 0);

      return boroughs;
    },
    roundedStatsUsers() {
      if (this.stats.users) {
        return Math.max(10, Math.floor(this.stats.users / 10) * 10);
      }

      return 200;
    },
    stats() {
      return this.$store.state.stats.data;
    },
    userLoggedIn() {
      return !!this.$store.state.user;
    },
    userRegistered() {
      if (!this.$store.state.user) {
        return false;
      }

      const requiredFields = [
        "name",
        "last_name",
        "date_of_birth",
        "address",
        "postal_code",
        "phone",
      ];

      for (let i = 0, len = requiredFields.length; i < len; i += 1) {
        if (!this.$store.state.user[requiredFields[i]]) {
          return false;
        }
      }

      return true;
    },
  },
  updated() {
    if (this.isLoggedIn && !this.isGlobalAdmin && !this.hasCompletedRegistration) {
      this.$router.push("register");
    }
  },
  i18n: {
    messages: {
      fr: {
        ...locales.fr.views.home,
      },
    },
  },
};
</script>

<style lang="scss"></style>
