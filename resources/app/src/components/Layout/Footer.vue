<template>
  <footer class="layout-footer">
    <b-container fluid>
      <b-row>
        <b-col class="text-center">
          <div class="layout-footer__logo">
            <a href="https://solon-collectif.org" target="_blank">
              <img src="/partners/logo-solon.png" />
            </a>
          </div>

          <div class="layout-footer__text">
            <p>
              LocoMotion est un projet citoyen accompagné par Solon.<br />

              <strong>locomotion.app</strong> est un
              <a href="https://www.gnu.org/philosophy/free-sw.fr.html" target="_blank"
                >logiciel libre</a
              >. Vous pourrez bientôt
              <a href="https://gitlab.com/Solon-collectif/locomotion.app" target="_blank"
                >contribuer</a
              >!<br />

              Version BETA &mdash; On expérimente ensemble sur locomotion.app.
              <a :href="googleFormUrl" target="_blank">
                Donnez-nous votre avis ou signalez un bug!
              </a>
            </p>

            <p><a href="/conditions">Conditions d'utilisation</a></p>
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
