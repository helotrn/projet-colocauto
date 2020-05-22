<template>
  <footer class="layout-footer">
    <b-container fluid>
      <b-row>
        <b-col>
          <div class="layout-footer__text">
            <strong>locomotion.app</strong> est un <a href="https://www.gnu.org/philosophy/free-sw.fr.html" target="_blank">logiciel libre</a>
            Vous pourrez bientôt contribuer au code!
            &mdash; <a href="/conditions">Conditions d'utilisation</a>
          </div>
        </b-col>
      </b-row>
    </b-container>
  </footer>
</template>

<script>
export default {
  name: 'LayoutFooter',
};
</script>

<style lang="scss">
$footer-height: 44px !default;

.layout-footer .container-fluid {
  background-color: $locomotion-light-green;
  height: $footer-height;

  display: flex;
  flex-direction: column;
  justify-content: space-around;

  .layout-footer__text {
    font-size: 14px;
    line-height: 16px;
    text-align: center;

    color: $white;

    a {
      color: $white;
      text-decoration: none;

      &:hover {
        text-decoration: none;
        color: $white;
      }
    }
  }
}
</style>
