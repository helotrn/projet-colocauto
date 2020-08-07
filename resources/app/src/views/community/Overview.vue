<template>
  <layout-page name="community-overview" v-if="routeDataLoaded" wide>
    <div>
      <gmap-map
        class="community-overview__map"
        ref="map"
        :zoom="zoom"
        :center="center"
        :options="mapOptions"
        map-type-id="terrain">
        <gmap-polygon v-for="c in communities" :key="`polygon-${c.id}`"
          :path="c.area_google"
          :label="c.name"
          :options="polygonOptions"
          @click="community = c" />
        <gmap-marker v-for="c in communities" :key="`marker-${c.id}`"
          :icon="{
            url: '/pins/default-pin.svg',
            scaledSize: {
              width: 28,
              height: 40,
            },
          }"
          :clickable="false"
          :position="c.center_google" />
      </gmap-map>
    </div>
  </layout-page>
</template>

<script>
import { gmapApi } from 'vue2-google-maps';

import DataRouteGuards from '@/mixins/DataRouteGuards';

export default {
  name: 'Overview',
  mixins: [DataRouteGuards],
  data() {
    return {
      mapIcon: {
        url: 'perdu.com',
      },
      mapOptions: {
        streetViewControl: false,
        fullscreenControl: false,
        mapTypeControl: false,
        styles: [
          {
            featureType: 'poi',
            stylers: [
              { visibility: 'off' },
            ],
          },
        ],
      },
      polygonOptions: {
        fillColor: '#16a59e',
        fillOpacity: 0.5,
        strokeOpacity: 0,
      },
      zoom: 1,
    };
  },
  computed: {
    center() {
      const center = this.communities.reduce((acc, c) => [
        (acc[0] + c.center[0]) / 2,
        (acc[1] + c.center[1]) / 2,
      ], this.communities[0].center);
      return {
        lat: center[0],
        lng: center[1],
      };
    },
    communities() {
      return this.$store.state.stats.data.communities;
    },
    google: gmapApi,
  },
  methods: {
    resetCenter() {
      const { LatLngBounds } = this.google.maps;
      const bounds = new LatLngBounds();

      this.communities.forEach(c => bounds.extend(c.center_google));
      this.$refs.map.fitBounds(bounds);
    },
  },
  watch: {
    routeDataLoaded() {
      setTimeout(() => {
        this.$refs.map.$mapPromise.then(() => {
          this.resetCenter();
        });
      }, 500);
    },
  },
};
</script>

<style lang="scss">
.community-overview {
  &__map {
    width: 100vw;
    height: calc(100vh - #{$layout-navbar-height} - 1px);
    z-index: 10;
  }
}
</style>
