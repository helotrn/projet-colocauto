<template>
  <layout-page name="insurance" class="insurance__content">
    <b-row tag="section" class="page__section">
      <b-col>
        <iframe src="http://localhost:8000/assurances.html" border="0" width="100%" />
      </b-col>
    </b-row>
  </layout-page>
</template>

<script>
export default {
  name: 'Privacy',
};
</script>

<style lang="scss">
.insurance__content {
  iframe {
    border: 0;
    margin: 0;
    height: calc(100vh - #{$layout-navbar-height + $footer-height});
  }
}
</style>
