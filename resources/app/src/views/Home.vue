<template>
  <layout-page name="home" wide>
    <section class="page__section page__section--top mb-0">
      <b-container>
        <h1 class="page__section__title">
          Communautés <br class="d-lg-none">mobiles <br>et solidaires
        </h1>

        <div class="page__section__content">
          <b-button size="lg" variant="warning" to="register"
            v-if="!userRegistered">S'inscrire</b-button>
          <b-button v-if="!userLoggedIn" size="lg" variant="outline-light" to="login">
            Se connecter
          </b-button>
          <b-button v-else size="lg" variant="outline-light" to="/app">
            Tableau de bord
          </b-button>
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
            Partager avec ses voisin-e-s des voitures, vélos et remorques à
            vélos de manière conviviale et sécuritaire.
          </p>
          <svg-city class="d-lg-none" />
          <svg-city-desktop class="d-none d-lg-block" />
        </div>
      </b-col>
    </section>

    <section>
      <dashboard-covid-section />
    </section>

    <section class="page__section page__section--what-is-it">
      <b-container>
        <h2 class="page__section__title mb-4">
          C’est quoi<br>LocoMotion?
        </h2>

        <div class="page__section__content">
          <b-row align-v="end">
            <b-col md="4">
              <b-row align-v="center">
                <b-col md="12">
                  <svg-borrow class="svg-borrow" />
                </b-col>
                <b-col md="12">
                  <p class="text-center mb-5">
                    Empruntez une auto à 5 minutes à pied
                  </p>
                </b-col>
              </b-row>
            </b-col>

            <b-col md="4">
              <b-row align-v="center">
                <b-col md="12">
                  <svg-lend class="svg-lend" />
                </b-col>
                <b-col md="12">
                  <p class="text-center mb-5">
                    Partagez votre auto à votre voisine
                  </p>
                </b-col>
              </b-row>
            </b-col>

            <b-col md="4">
              <b-row align-v="center">
                <b-col md="12">
                  <svg-tridem class="svg-tridem" />
                </b-col>
                <b-col md="12">
                  <p class="text-center mb-5">
                    Faites partie d’un projet collectif ambitieux
                  </p>
                </b-col>
              </b-row>
            </b-col>
          </b-row>
        </div>
      </b-container>
    </section>

    <section class="page__section page__section--participants">
      <b-container fluid>
        <b-row>
          <b-col lg="6">
            <b-row class="page__section--participants__photos">
              <b-col col class="page__section--participants__photo">
                <img src="/user1.png">
              </b-col>
              <b-col col class="page__section--participants__photo">
                <img src="/user2.png">
              </b-col>
              <b-col col class="page__section--participants__photo">
                <img src="/user3.png">
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
                Aujourd’hui, <span class="no-break">{{ stats.communities.length }}</span> quartiers,
                soit plus de <span class="no-break">{{ stats.users }} participant•e•s</span>
                partageant <span class="no-break">{{ stats.loanables }}</span> véhicules
                expérimentent cette solution collective.
              </p>

              <div>
                <b-button size="lg" variant="outline-light" to="/communities">
                  Situer les voisinages
                </b-button>
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
              Rejoignez un voisinage
            </h2>

            <p class="mb-5">
              Participez à la vie de quartier en vous déplaceant autrement.
              Des voisins et des voisines s'activent, prenez part au mouvement!
            </p>

            <b-button size="lg" variant="warning" to="register">S'inscrire</b-button>
          </b-col>
        </b-row>
      </b-container>
    </section>

    <section class="page__section page__section--faq">
      <b-container>
        <b-row>
          <b-col>
            <div class="page__section__header">
              <h2 class="page__section__title">
                {{ $t('faq.title') }}
              </h2>
              <b-button size="lg" variant="outline-secondary" to="faq">Visiter la FAQ</b-button>
            </div>
            <div class="page__section--faq__questions">
              <div role="tablist">
                <home-faq-item id="1"
                  :title="$t('faq.sections.why.questions.0.title')">
                  <p v-html="$t('faq.sections.why.questions.0.content')" />
                </home-faq-item>

                <home-faq-item id="2" :title="$t('faq.sections.insurance.questions.0.title')">
                  <div v-html="$t('faq.sections.insurance.questions.0.content')" />
                </home-faq-item>

                <home-faq-item id="3" :title="$t('faq.sections.costs.questions.0.title')">
                  <div v-html="$t('faq.sections.costs.questions.0.content')" />
                </home-faq-item>

                <home-faq-item id="4" :title="$t('faq.sections.why.questions.1.title')">
                  <p v-html="$t('faq.sections.why.questions.1.content')" />
                </home-faq-item>

                <home-faq-item id="5" :title="$t('faq.sections.participate.questions.0.title')">
                  <div v-html="$t('faq.sections.participate.questions.0.content')" />
                </home-faq-item>
              </div>
            </div>
          </b-col>
        </b-row>
      </b-container>
    </section>

    <section class="page__section page__section--footer d-none d-lg-block">
      <svg-footer-dot />
    </section>

    <section class="page__section page__section--partners">
      <b-container>
        <b-row>
          <b-col>
            <h2 class="page__section__title">Partenaires</h2>

            <svg-footer-dot class="page__section--partners__dot d-lg-none" />

            <div class="page__section--partners__partners">
              <div class="page__section--partners__partners__partner">
                <img src="/partners/Logo_Mtl_Ahuntsic-Cartierville.svg">
              </div>

              <div class="page__section--partners__partners__partner">
                <img src="/partners/logo-chaire-mobilite.png">
              </div>

              <div class="page__section--partners__partners__partner">
                <img src="/partners/f20-logo-n1-desjardins-v.svg">
              </div>

              <div class="page__section--partners__partners__partner">
                <img src="/partners/FCM-logo-2018-col_fr.png">
              </div>

              <div class="page__section--partners__partners__partner">
                <img src="/partners/Logo_Mtl_Rosemont-La-Petite-Patrie.svg">
              </div>
            </div>
          </b-col>
        </b-row>
      </b-container>
    </section>
  </layout-page>
</template>

<script>
import DashboardCovidSection from '@/components/Dashboard/CovidSection.vue';
import HomeFaqItem from '@/components/Home/FaqItem.vue';

import DataRouteGuards from '@/mixins/DataRouteGuards';

import Borrow from '@/assets/svg/home-borrow.svg';
import CityDesktop from '@/assets/svg/home-city-desktop.svg';
import City from '@/assets/svg/home-city.svg';
import CloudsDesktop from '@/assets/svg/home-clouds-desktop.svg';
import Clouds from '@/assets/svg/home-clouds.svg';
import CommunityDesktop from '@/assets/svg/home-community-desktop.svg';
import Community from '@/assets/svg/home-community.svg';
import DotDesktop from '@/assets/svg/home-dot-desktop.svg';
import Dot from '@/assets/svg/home-dot.svg';
import FooterDot from '@/assets/svg/home-footer-dot.svg';
import Lend from '@/assets/svg/home-lend.svg';
import Tridem from '@/assets/svg/home-tridem.svg';
import WaveDesktop from '@/assets/svg/home-wave-desktop.svg';
import Wave from '@/assets/svg/home-wave.svg';

export default {
  name: 'Home',
  mixins: [DataRouteGuards],
  components: {
    DashboardCovidSection,
    HomeFaqItem,
    'svg-borrow': Borrow,
    'svg-city-desktop': CityDesktop,
    'svg-city': City,
    'svg-clouds-desktop': CloudsDesktop,
    'svg-clouds': Clouds,
    'svg-community-desktop': CommunityDesktop,
    'svg-community': Community,
    'svg-dot-desktop': DotDesktop,
    'svg-dot': Dot,
    'svg-footer-dot': FooterDot,
    'svg-lend': Lend,
    'svg-tridem': Tridem,
    'svg-wave-desktop': WaveDesktop,
    'svg-wave': Wave,
  },
  computed: {
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
        'name', 'last_name', 'date_of_birth',
        'address', 'postal_code', 'phone',
      ];

      for (let i = 0, len = requiredFields.length; i < len; i += 1) {
        if (!this.$store.state.user[requiredFields[i]]) {
          return false;
        }
      }

      return true;
    },
  },
};
</script>

<style lang="scss">
</style>
