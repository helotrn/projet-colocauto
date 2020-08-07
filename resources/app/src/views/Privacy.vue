<template>
  <layout-page name="cgu" class="cgu__content">
    <b-row tag="section" class="page__section">
      <b-col>
        <iframe src="/cgu.html" border="0" width="100%" />
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
.cgu__content {
  iframe {
    border: 0;
    margin: 0;
    height: calc(100vh - #{$layout-navbar-height});
  }
}
</style>
