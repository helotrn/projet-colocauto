<template>
  <layout-page name="home" wide>
    <section class="page__section page__section--top">
      <b-container>
        <h1 class="page__section__title">
          Communautés <br class="d-lg-none">mobiles <br>et solidaires
        </h1>
        <div class="page__section__content">
          <b-btn variant="warning" to="register">S'inscrire</b-btn>
          <b-btn variant="outline-light" to="login">Se connecter</b-btn>
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
            Partager avec ses voisin-e-s des voitures, des vélos électriques et des remorques à
            vélo de manière sécuritaire et conviviale.
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
                  <p>Prête ton auto à tes voisins</p>
                </b-col>
              </b-row>
            </b-col>
            <b-col md="4">
              <b-row align-v="center">
                <b-col cols="6" md="12">
                  <svg-borrow class="svg-borrow" />
                </b-col>
                <b-col cols="6" md="12">
                  <p>Fais partie d’une économie circulaire</p>
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
              Plus de 200 participant.e.s!
            </h2>

            <p>
              Aujourd’hui, 3 quartiers montréalais et 1 quartier sherbrookois, soit plus de
              100 participant•e•s expérimentent cette solution collective.
            </p>

            <b-btn variant="outline-light" to="zones">Situer les voisinnages</b-btn>
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
              Rejoins ta communauté
            </h2>
            <p>
              Des citoyen-ne-s en mouvement qui transforment leur quartier par la connexion avec
              l'autre et la possibilité de se déplacer autrement.
            </p>
            <b-btn variant="warning" to="register">S'inscrire</b-btn>
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
              <b-btn variant="outline-secondary" to="faq">Visiter la FAQ</b-btn>
            </div>
            <div class="page__section--faq__questions">
              <div role="tablist">
                <b-card no-body>
                  <b-card-header header-tag="header" role="tab">
                    <b-button block href="#" v-b-toggle.accordion-1 variant="white-secondary">
                      Qu'est-ce qui arrive en cas d'accident ?
                    </b-button>
                  </b-card-header>
                  <b-collapse id="accordion-1" accordion="my-accordion" role="tabpanel">
                    <b-card-body>
                      <b-card-text>
                        Lorem ipsum dolor sit amet, consectetur adipisicing elit. Eos, tenetur.
                      </b-card-text>
                    </b-card-body>
                  </b-collapse>
                </b-card>
                <b-card no-body>
                  <b-card-header header-tag="header" role="tab">
                    <b-button block href="#" v-b-toggle.accordion-2 variant="white-secondary">
                      Pourquoi ferais-je confiance à des personnes inconnues
                      pour leur prêter mon auto ?
                    </b-button>
                  </b-card-header>
                  <b-collapse id="accordion-2" accordion="my-accordion" role="tabpanel">
                    <b-card-body>
                      <b-card-text>
                        Lorem ipsum dolor sit amet, consectetur adipisicing elit. Obcaecati illo,
                        dicta. Voluptate reiciendis amet laborum quod, alias quisquam excepturi
                        culpa?
                      </b-card-text>
                    </b-card-body>
                  </b-collapse>
                </b-card>
                <b-card no-body>
                  <b-card-header header-tag="header" role="tab">
                    <b-button block href="#" v-b-toggle.accordion-3 variant="white-secondary">
                      Est-ce que c’est juste du partage de voitures ?
                    </b-button>
                  </b-card-header>
                  <b-collapse id="accordion-3" accordion="my-accordion" role="tabpanel">
                    <b-card-body>
                      <b-card-text>
                        Lorem ipsum dolor sit amet, consectetur adipisicing elit. Mollitia
                        incidunt iste eos at qui veritatis.
                      </b-card-text>
                    </b-card-body>
                  </b-collapse>
                </b-card>
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
import Authenticated from '@/mixins/Authenticated';
import Notification from '@/mixins/Notification';

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
  mixins: [Authenticated, Notification],
  components: {
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
};
</script>

<style lang="scss">
@import "~bootstrap";
@import "../assets/scss/tools";

.page.home {
  .page__section {
    @include space("padding-top", 36px, "", "", 65px);
    @include space("padding-bottom", 36px, "", "", 65px);
  }

  .page__section__title {
    @include font-size(28px, "", "", 50px);
    color: $secondary;
  }

  p {
    color: $secondary;
    @include font-size(18px, "", "", 20px);
  }

  .page__section--top {
    height: calc(100vh - #{$layout-navbar-height});
    min-height: 604px;
    background: url("/home-top.jpg") center top;
    background-size: cover;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    position: relative;
    overflow: hidden;
    @include space("padding-bottom", 122px, "", "", 80px);

    .page__section__title {
      color: $white;
      @include font-size(36px, "", "", 65px);
      @include space("margin-bottom", 20px, "", "", 0);
    }

    .wave-svg {
      position: absolute;
      bottom: 0;
      height: auto;
      left: 0;
      width: 100%;
    }

    .wave-dot {
      position: absolute;
      z-index: 2;
      bottom: 50px;
      width: 100%;
      height: auto;
      left: 0;
      @include position("bottom", 50px, "", "", 165px);
    }

    .container {
      position: relative;
      z-index: 3;
      @include media-breakpoint-up(lg) {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
      }
    }

    .btn {
      margin-right: 10px;
    }
  }

  .page__section--hero {
    text-align: center;

    p {
      color: $secondary;
      @include font-size(22px, "", "", 30px);
      max-width: 611px;
      margin-left: auto;
      margin-right: auto;
    }

    svg {
      margin: 0 auto;
    }
  }

  .page__section--what-is-it {
    background: #f5f8fb;
    margin-bottom: 0;
    text-align: center;
    @include space("padding-bottom", 136px, "", "", 65px);

    .page__section__title {
      @include space("margin-bottom", 15px, "", "", 30px);
    }

    p {
      max-width: 250px;
      margin: 20px auto;
      text-align: left;

      &:last-child {
        margin-bottom: 0;
      }

      @include media-breakpoint-up(md) {
        text-align: center;
      }

    }

    .svg-lend {
      max-width: 229px;
    }

    .svg-borrow {
      max-width: 248px;
    }

  }

  .page__section--participants {
    background: #1e4847;
    text-align: center;
    @include space("padding-top", 65px, "", "", 100px);
    @include space("padding-bottom", 90px, "", "", 100px);

    .page__section__title {
      color: $white;
    }

    .page__section--participants__photos {
      @include size("max-width", 90%, "", "", 490px);
      @include space("margin", -125px auto 30px, "", "", 0 auto);
    }

    .page__section--participants__photo {
      padding: 0 5px;
    }

    p {
      color: $white;
      max-width: 375px;
      margin-left: auto;
      margin-right: auto;
    }

    img {
      max-width: 100%;
      height: auto;
    }

  }

  .page__section--community {
    background: none;
    text-align: center;

    p {
      max-width: 450px;
      margin-left: auto;
      margin-right: auto;
    }

    .svg-community {
      position: relative;
      @include size("width", 283px, "", "", 820px);
      @include space("margin-top", -150px, "", "", -260px);
      @include position("left", 0, "", "", -10vw );
    }
  }

  .page__section--faq {
    background: #f5f8fb;
    position: relative;
    margin: 0;

    .container {
      max-width: 700px;
      z-index: 2;
      position: relative;
    }

    .page__section__header {
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      margin-bottom: 35px;
    }

    .svg-footer {
      @extend .d-none;
      @extend .d-lg-block;
      position: absolute;
      right: 10vw;
      bottom: 0;
    }
  }

  .page__section--footer {
    height: 136px;
    overflow: hidden;
    position: relative;
    padding: 0;
    margin: 0;

    svg {
      position: absolute;
      left: 0;
      bottom: 30px;
    }
  }

}

</style>
