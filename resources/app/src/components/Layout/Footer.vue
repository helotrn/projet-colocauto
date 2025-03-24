<template>
  <footer class="layout-footer">
    <b-container fluid>
      <b-row class="justify-content-md-center">
        <b-col class="text-center">
          <div class="layout-footer__logo">
            <a href="https://www.mobicoop.fr/" target="_blank">
              <img src="/partners/logo-mobicoop.png" />
            </a>
          </div>

          <div class="layout-footer__text w-75 m-auto">
            <p class="introduction">
              <strong>Coloc'Auto</strong> est porté par <a href="https://www.mobicoop.fr" target="_blank">Mobicoop</a> :
              une coopérative qui travaille depuis 2008 avec les citoyens, les entreprises et les acteurs
              publics pour promouvoir les mobilités partagées.
            </p>
            <p>Cette application est basée sur <a href="https://locomotion.app/" target="_blank">LocoMotion.app</a>,
            un projet citoyen actuellement à l'œuvre au Québec et accompagné par Solon.
            <a href="https://solon-collectif.org/action/locomotion/" target="_blank">En savoir plus</a>
            </p>

            <p class="cgu small">
              <a href="https://www.colocauto.org/conditions"
                >Conditions Générales</a
              >
              - <a href="https://www.colocauto.org/confidentialite"
                >Politique de confidentialité</a
              >
              - <a href="https://www.colocauto.org/faq"
                >F.A.Q.</a
              >
              - <a href="mailto:soutien@colocauto.org">Nous écrire</a>
              - <a href="https://gitlab.com/mobicoop/colocauto"
                >Gitlab</a>
            </p>
          </div>
        </b-col>
      </b-row>
    </b-container>
  </footer>
</template>

<script>
import instagram from "@/assets/svg/Instagram_Glyph_White.svg";

export default {
  name: "LayoutFooter",
  components: {
    "insta-icon": instagram,
  },
  data() {
    return {
      googleFormUrl:
        "https://docs.google.com/forms/d/e/" +
        "1FAIpQLSd-O_aK6WycUjD2Vd3Xsle_UwV61P84-3kFZGzWAzeASg2bHA/viewform",
    };
  },
};
</script>

<style lang="scss">
.layout-footer .container-fluid {
  background-color: $locomotion-light-green;

  display: flex;
  flex-direction: column;
  justify-content: space-around;

  .layout-footer__logo {
    text-align: center;

    img {
      width: 200px;
      &:hover {
        opacity: 0.8;
      }
    }
  }

  .layout-footer__text {
    font-size: 14px;
    line-height: 18px;

    color: $white;

    .introduction {
      a {
        text-decoration: none;
      }
    }

    a {
      color: $white;
      text-decoration: underline;

      &:hover {
        text-decoration: none;
        color: $white;
      }
    }
  }
  .social-media-icons {
    display: flex;
    gap: 1rem;
    align-items: center;
    justify-content: center;
    img,
    svg {
      width: 2rem;

      &:hover {
        opacity: 0.8;
      }
    }
  }
}
</style>
