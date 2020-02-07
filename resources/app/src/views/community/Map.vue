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
  name: 'Map',
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
        if (this.$store.state['register.map'].center) {
          return this.$store.state['register.map'].center;
        }

        if (this.community) {
          return this.community.center_google;
        }

        return this.averageCommunitiesCenter;
      },
      set(center) {
        this.$store.commit('register.map/center', center);
      },
    },
    google: gmapApi,
  },
};
</script>

<style lang="scss">
.register-map.page {
  .register-map-page {
    position: relative;

    &__map {
      width: 100vw;
      height: calc(100vh - #{$layout-navbar-height + $molotov-footer-height});
      z-index: 10;
    }

    .card {
      width: 190px;
    }

    &__form {
      margin-top: 50px;
      margin-left: 30px;
      position: absolute;
      z-index: 100;
    }
  }
}
</style>
