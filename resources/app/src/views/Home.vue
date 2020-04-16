<template>
  <layout-page name="home" wide>
    <section class="page__section page__section--top">
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

    <section class="page__section page__section--hero">
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

    <section class="page__section page__section--what-is-it">
      <b-container>
        <h2 class="page__section__title">
          C’est quoi LocoMotion?
        </h2>

        <div class="page__section__content">
          <b-row align-v="end">
            <b-col md="4">
              <b-row align-v="center">
                <b-col cols="6" md="12">
                  <svg-borrow class="svg-borrow" />
                </b-col>
                <b-col cols="6" md="12">
                  <p>Emprunte des véhicules à 5 min à pieds de chez toi</p>
                </b-col>
              </b-row>
            </b-col>

            <b-col md="4">
              <b-row align-v="center">
                <b-col cols="6" md="12" order="2" order-md="1" >
                  <svg-lend class="svg-lend" />
                </b-col>
                <b-col cols="6" md="12" order="1" order-md="2">
                  <p>Partage ton auto avec tes voisin-e-s</p>
                </b-col>
              </b-row>
            </b-col>

            <b-col md="4">
              <b-row align-v="center">
                <b-col cols="6" md="12">
                  <svg-borrow class="svg-borrow" />
                </b-col>
                <b-col cols="6" md="12">
                  <p>Fais partie d’un projet collectif ambitieux</p>
                </b-col>
              </b-row>
            </b-col>
          </b-row>
        </div>
      </b-container>
    </section>

    <section class="page__section page__section--participants">
      <b-container>
        <b-row>
          <b-col lg="6">
            <b-row class="page__section--participants__photos">
              <b-col cols="4" class="page__section--participants__photo">
                <img src="/user1.png">
              </b-col>
              <b-col cols="4" class="page__section--participants__photo">
                <img src="/user2.png">
              </b-col>
              <b-col cols="4" class="page__section--participants__photo">
                <img src="/user3.png">
              </b-col>
            </b-row>
          </b-col>
          <b-col lg="6">
            <h2 class="page__section__title">
              Plus de 200 <span class="no-break">participant-e-s!</span>
            </h2>

            <p>
              Aujourd’hui, <span class="no-break">{{ stats.communities }}</span> quartiers,
              soit plus de <span class="no-break">{{ stats.users }} participant•e•s</span>
              partageant <span class="no-break">{{ stats.loanables }}</span> véhicules
              expérimentent cette solution collective.
            </p>

            <b-button size="lg" variant="outline-light" to="/communities">
              Situer les voisinnages Locomotion
            </b-button>
          </b-col>
        </b-row>
      </b-container>
    </section>

    <section class="page__section page__section--community">
      <b-container>
        <b-row align-v="center">
          <b-col lg="6" order-lg="2">
            <svg-community class="d-lg-none svg-community" />
            <svg-community-desktop class="d-none d-lg-block svg-community" />
          </b-col>
          <b-col lg="6">
            <h2 class="page__section__title">
              Rejoins ta voisinage
            </h2>
            <p>
              Se déplacer autrement et entrer en relation aux autres transforment votre coin.<br>
              Des voisins et des voisines s’activent, prenez part au mouvement!
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
                FAQ
              </h2>
              <b-button size="lg" variant="outline-secondary" to="faq">Visiter la FAQ</b-button>
            </div>
            <div class="page__section--faq__questions">
              <div role="tablist">
                <home-faq-item id="1"
                  title="Comment faire confiance à mon voisinage pour partager mon auto?">
                  <p>
                    Comme propriétaire de voiture, vous avez toujours le choix d’accepter ou non
                    une demande de réservation. Chaque participant-e nous fournit ses pièces
                    d’identité et son dossier de conduite. Ainsi, nous connaissons les membres
                    des voisinages LocoMotion. Aussi, en apprenant à connaître vos voisin-e-s, il
                    est plus facile de se faire confiance. Et enfin, n’oubliez pas qu’il y a une
                    assurance!
                  </p>
                </home-faq-item>

                <home-faq-item id="2" title="Qu'est-ce qui arrive en cas d'accident?">
                  <p>
                    Si vous avez un accident, signalez-le sur la plateforme et suivez les
                    instructions.
                  </p>
                  <p>
                    En cas d’accident, notre couverture Desjardins Assurances prend en charge
                    la responsabilité civile et les réparations. Une fois votre inscription
                    complétée, nous vous fournirons tous les détails.
                  </p>
                  <ul>
                    <li>
                      Couverture responsabilité civile à hauteur de 2M$
                    </li>
                    <li>
                      Franchise de 100$ pour les dommages causés notamment par le feu, le vol,
                      le vandalisme, le bris de vitres.
                    </li>
                    <li>
                      Franchise de 500$ pour les dommages causés par une collision ou un versement
                    </li>
                  </ul>
                </home-faq-item>

                <home-faq-item id="3" title="Combien ça coûte?">
                  <p>
                    L’inscription est gratuite. Et des véhicules se partagent à un prix juste
                    pour tout le voisinage!
                  </p>
                  <ul>
                    <li>
                      Recevez une contribution des personnes qui utilisent votre voiture.
                    </li>
                    <li>
                      Vous utilisez la voiture de votre voisin-e ?
                    </li>
                    <li>
                      Participez à la compensation pour son usure, l’essence et l’assurance.
                    </li>
                    <li>
                      Profitez des véhicules collectifs à deux roues : c’est gratuit!
                    </li>
                  </ul>
                </home-faq-item>

                <home-faq-item id="4" title="Qu’est-ce qu’il y a d’autres que des voitures?">
                  <p>
                    En plus des voitures variées de vos voisins et voisines, vous aurez accès à une
                    variété de moyens de transport : vélo-cargo, vélo électrique, remorque à
                    attacher à votre vélo…  Vous pourrez le choisir selon votre besoin. LocoMotion
                    c’est la cerise de votre
                    <a href="https://equiterre.org/solution/cocktail-transport" target="_blank">
                      cocktail transport
                    </a>!
                  </p>
                </home-faq-item>

                <home-faq-item id="5" title="Alors, vous embarquez?">
                  <p>
                    Votre mission si vous l’acceptez : faire vivre votre voisinage! Plus une
                    communauté renforce ses liens, plus le partage sera agréable et facile! C’est
                    la conclusion - spoiler alert! - du projet de Nos milieux de vie!, de la
                    Chaire de recherche qui nous étudié.
                  </p>
                  <p>
                    Bref,dans votre voisinage :  aidez le nouveau monde, organisez des 5 à 7,
                    parlez-en! Il y a plein de rôles à jouer, prenez part à ce mouvement collectif!
                  </p>
                </home-faq-item>
              </div>
            </div>
          </b-col>
        </b-row>
      </b-container>
      <svg-footer class="svg-footer" />
    </section>

    <section class="page__section page__section--footer">
      <svg-footer-dot />
    </section>
  </layout-page>
</template>

<script>
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
import Footer from '@/assets/svg/home-footer.svg';
import Lend from '@/assets/svg/home-lend.svg';
import WaveDesktop from '@/assets/svg/home-wave-desktop.svg';
import Wave from '@/assets/svg/home-wave.svg';

export default {
  name: 'Home',
  mixins: [DataRouteGuards],
  components: {
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
    'svg-footer': Footer,
    'svg-lend': Lend,
    'svg-wave-desktop': WaveDesktop,
    'svg-wave': Wave,
  },
  computed: {
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
