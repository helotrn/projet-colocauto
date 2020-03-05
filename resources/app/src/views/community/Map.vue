<template>
  <layout-page name="community-map" wide>
    <gmap-map class="community-map-page__map"
      ref="map"
      :center="center"
      :zoom="14"
      :options="mapOptions"
      map-type-id="terrain" />
  </layout-page>
</template>

<script>
import { gmapApi } from 'vue2-google-maps';

import DataRouteGuards from '@/mixins/DataRouteGuards';

export default {
  name: 'CommunityMap',
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
    };
  },
  computed: {
    center: {
      get() {
        if (this.$store.state['community.map'].center) {
          return this.$store.state['community.map'].center;
        }

        if (this.community) {
          return this.community.center_google;
        }

        return this.averageCommunitiesCenter;
      },
      set(center) {
        this.$store.commit('community.map/center', center);
      },
    },
    google: gmapApi,
  },
};
</script>

<style lang="scss">
</style>
