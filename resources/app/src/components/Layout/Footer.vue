<template>
  <footer class="layout-footer">
    <b-container fluid>
      <b-row>
        <b-col>
          <div class="layout-footer__text">
            <strong>locomotion.app</strong> est un <a href="https://www.gnu.org/philosophy/free-sw.fr.html" target="_blank">logiciel libre</a> <br>
            Vous pourrez bientôt contribuer au code!
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
  background-color: $black;
  height: $footer-height;

  .layout-footer__text {
    margin-top: 6px;
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
