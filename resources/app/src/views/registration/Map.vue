<template>
  <layout-page name="registration" wide>
    <gmap-map class="registration-page__map"
      :center="averageCommunitiesCenter"
      :zoom="14"
      map-type-id="terrain">
      <gmap-polygon v-for="community in communities" :key="community.id"
        :path="community.area_google"
        @click="showModal(community.id)" />
    </gmap-map>
  </layout-page>
</template>

<script>
import DataRouteGuards from '@/mixins/DataRouteGuards';

export default {
  name: 'Map',
  mixins: [DataRouteGuards],
  computed: {
    averageCommunitiesCenter() {
      const { data: communities } = this.$store.state.communities;
      const center = communities.reduce((acc, c) => [
        (acc[0] + c.center[0]) / 2,
        (acc[1] + c.center[1]) / 2,
      ], communities[0].center)
      return {
        lat: center[0],
        lng: center[1],
      };
    },
    communities() {
      return this.$store.state.communities.data;
    },
    paths() {
      return this.communities.map(c => c);
    }
  },
  methods: {
    showModal(community) {

    },
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
