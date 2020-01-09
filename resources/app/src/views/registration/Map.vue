<template>
  <layout-page name="registration" wide>
    <gmap-map class="registration-page__map"
      :center="{ lat:10, lng:10 }"
      :zoom="7"
      map-type-id="terrain" />
  </layout-page>
</template>

<script>
export default {
  name: 'Map',
  async beforeMount() {
    const { loaded } = this.$store.state.communities;
    if (!loaded) {
      this.$store.dispatch('communities/load');
    }
  },
};
</script>

<style lang="scss">
.registration.page {
  .registration-page__map {
    width: 100vw;
    height: calc(100vh - #{$layout-navbar-height + $molotov-footer-height});
  }
}
</style>
