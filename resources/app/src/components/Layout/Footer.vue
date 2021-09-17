<template>
  <footer class="layout-footer">
    <b-container fluid>
      <b-row class="justify-content-md-center">
        <b-col class="text-center">
          
          <div class="layout-footer__logo">
            <a href="https://solon-collectif.org" target="_blank">
              <img src="/partners/logo-solon.png" />
            </a>
          </div>

          <div class="layout-footer__text">
              <p class="introduction">
              <strong>LocoMotion</strong> est un projet citoyen accompagné par <a href="https://solon-collectif.org">Solon</a>. <br>
              </p>

            <p>
              Ce site web s'améliore en continu. Merci de nous <a :href="googleFormUrl" target="_blank">envoyer vos idées et suggestions</a>.
            </p>

            <p class="cgu small">Organisme à but non lucratif basé à Montréal - <a href="/conditions" title="Conditions d'utilisation - LocoMotion">Conditions d'utilisation</a></p>
          </div>
        </b-col>
      </b-row>
    </b-container>
  </footer>
</template>

<script>
export default {
  name: "LayoutFooter",
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
    }
  }

  .layout-footer__text {
    font-size: 14px;
    line-height: 18px;

    color: $white;

    .introduction {
      a {
        text-decoration:none;
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
}
</style>
