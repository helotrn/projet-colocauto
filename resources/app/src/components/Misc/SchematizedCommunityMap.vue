<template>
  <b-row :class="`schematized-community-map${loaded ? ' loaded' : ''}`">
    <b-col class="schematized-community-map__neighborhoods" md="6">
      <p v-for="n in neighborhoods" :key="n.id">{{ n.name }}</p>
    </b-col>

    <b-col md="6">
      <gmap-map
        class="schematized-community-map__map"
        ref="schemamap"
        :center="center"
        :options="mapOptions"
        map-type-id="terrain">
        <gmap-polygon
        :path="borough.area_google"
        :label="borough.name"
        :options="boroughPolygonOptions" />
          <gmap-polygon v-for="c in neighborhoods" :key="`polygon-${c.id}`"
            :path="c.area_google"
            :label="c.name"
            :options="polygonOptions" />
      </gmap-map>
    </b-col>
  </b-row>
</template>

<script>
import { gmapApi } from 'vue2-google-maps';

export default {
  name: 'SchematizedCommunityMap',
  props: {
    borough: {
      required: true,
      type: Object,
    },
    neighborhoods: {
      required: true,
      type: Array,
    },
  },
  mounted() {
    setTimeout(() => {
      this.$refs.schemamap.$mapPromise.then(() => {
        const { LatLngBounds } = this.google.maps;
        const bounds = new LatLngBounds();

        this.neighborhoods.forEach(n => n.area_google.forEach(p => bounds.extend(p)));

        this.$refs.schemamap.fitBounds(bounds);
        this.loaded = true;
      });
    }, 500);
  },
  data() {
    const center = this.neighborhoods.reduce((acc, c) => [
      (acc[0] + c.center[0]) / 2,
      (acc[1] + c.center[1]) / 2,
    ], this.neighborhoods[0].center);

    return {
      boroughPolygonOptions: {
        fillColor: '#16a59e',
        fillOpacity: 0.3,
        strokeColor: '#16a59e',
        strokeOpacity: 0.7,
        zIndex: 1,
      },
      center: {
        lat: center[0],
        lng: center[1],
      },
      loaded: false,
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
        zIndex: 2,
      },
    };
  },
  computed: {
    google: gmapApi,
  },
};
</script>

<style lang="scss">
.schematized-community-map {
  opacity: 0;
  transition: opacity $one-tick ease-in-out;

  &__neighborhoods {
    font-size: 30px;

    p {
      margin-bottom: 3rem;
    }
  }

  &__map {
    width: 30%;
    min-width: 300px;
    margin: 0 auto;
    height: 300px;
  }

  &.loaded {
    opacity: 1;
  }
}
</style>
