<template>
  <layout-page name="community-overview" v-if="routeDataLoaded" wide>
    <div>
      <b-card
        class="community-overview__neighborhood"
        v-if="neighborhood"
        :title="`Quartier ${neighborhood ? neighborhood.name : ''}`"
      >
        <b-card-text>
          <div>
            <p class="community-overview__description" v-if="neighborhood.description">
              {{ neighborhood.description }}
            </p>
          </div>
        </b-card-text>
      </b-card>

      <b-card
        class="community-overview__borough"
        v-else-if="borough"
        :title="`Voisinage ${borough ? borough.name : ''}`"
      >
        <b-card-text>
          <div>
            <p class="community-overview__description" v-if="borough.description">
              {{ borough.description }}
            </p>
          </div>
        </b-card-text>
      </b-card>

      <gmap-map
        class="community-overview__map"
        ref="map"
        :zoom="zoom"
        :center="center"
        :options="mapOptions"
        map-type-id="terrain"
        @click="resetView()"
      >
        <gmap-polygon
          v-for="n in neighborhoods"
          :key="`polygon-${n.id}`"
          :path="n.area_google"
          :label="n.name"
          :options="neighborhoodPolygonOptions"
          @click="setNeighborhood(n)"
        />
        <gmap-polygon
          v-for="b in boroughs"
          :key="`polygon-${b.id}`"
          :path="b.area_google"
          :label="b.name"
          :options="boroughPolygonOptions"
          @click="setBorough(b)"
        />
        <gmap-marker
          v-for="c in communities"
          :key="`marker-${c.id}`"
          :icon="{
            url: '/pins/default-pin.svg',
            scaledSize: {
              width: 28,
              height: 40,
            },
          }"
          :position="c.center_google"
          :options="markerOptions"
          @click="setCommunity(c)"
        />
      </gmap-map>
    </div>
  </layout-page>
</template>

<script>
import { gmapApi } from "vue2-google-maps";

import DataRouteGuards from "@/mixins/DataRouteGuards";

export default {
  name: "Overview",
  mixins: [DataRouteGuards],
  data() {
    return {
      mapIcon: {
        url: "perdu.com",
      },
      mapOptions: {
        streetViewControl: false,
        fullscreenControl: false,
        mapTypeControl: false,
        styles: [
          {
            featureType: "poi",
            stylers: [{ visibility: "off" }],
          },
        ],
      },
      neighborhoodPolygonOptions: {
        fillColor: "#16a59e",
        fillOpacity: 0.3,
        strokeColor: "#16a59e",
        strokeOpacity: 0.7,
        zIndex: 1,
      },
      boroughPolygonOptions: {
        fillColor: "#16a59e",
        fillOpacity: 0.5,
        strokeOpacity: 0,
        zIndex: 2,
      },
      markerOptions: {
        zIndex: 3,
      },
      zoom: 1,
    };
  },
  computed: {
    center() {
      const center = this.communities.reduce(
        (acc, c) => [(acc[0] + c.center[0]) / 2, (acc[1] + c.center[1]) / 2],
        this.communities[0].center
      );
      return {
        lat: center[0],
        lng: center[1],
      };
    },
    neighborhood: {
      get() {
        return this.$store.state["community.map"].neighborhood;
      },
      set(neighborhood) {
        this.$store.commit("community.map/neighborhood", neighborhood);
      },
    },
    borough: {
      get() {
        return this.$store.state["community.map"].borough;
      },
      set(borough) {
        this.$store.commit("community.map/borough", borough);
      },
    },
    neighborhoods() {
      return this.$store.state.stats.data.communities.filter((c) => c.type === "borough");
    },
    boroughs() {
      return this.$store.state.stats.data.communities.filter((c) => c.type === "neighborhood");
    },
    communities() {
      return this.$store.state.stats.data.communities.filter((c) => c.type !== "private");
    },
    google: gmapApi,
  },
  methods: {
    centerOnCommunity(community) {
      const { LatLngBounds } = this.google.maps;
      const bounds = new LatLngBounds();

      community.area_google.forEach((p) => bounds.extend(p));

      this.$refs.map.fitBounds(bounds);
    },
    resetCenter() {
      const { LatLngBounds } = this.google.maps;
      const bounds = new LatLngBounds();

      this.communities.forEach((c) => bounds.extend(c.center_google));
      this.$refs.map.fitBounds(bounds);
    },
    resetView() {
      this.borough = null;
      this.neighborhood = null;
    },
    setNeighborhood(n) {
      this.neighborhood = n;
      this.borough = null;
    },
    setBorough(b) {
      this.borough = b;
      this.neighborhood = null;
    },
    setCommunity(c) {
      if (c.type === "borough") {
        this.neighborhood = c;
        this.borough = null;
      } else {
        this.neighborhood = null;
        this.borough = c;
      }
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
    borough(value) {
      this.resetCenter();
      if (!value) {
        return this.resetCenter();
      }

      return this.centerOnCommunity(value);
    },
    neighborhood(value) {
      this.resetCenter();

      if (!value) {
        return this.resetCenter();
      }

      return this.centerOnCommunity(value);
    },
  },
};
</script>

<style lang="scss">
@import "~bootstrap/scss/mixins/breakpoints";

.community-overview {
  &__map {
    width: 100vw;
    height: calc(100vh - #{$layout-navbar-height} - 1px);
    z-index: 10;
  }

  &__neighborhood.card,
  &__borough.card {
    margin-top: 50px;
    margin-left: 30px;
    position: absolute;
    z-index: 100;
    max-width: 500px;

    @include media-breakpoint-down(lg) {
      margin: 5%;
      min-width: 90%;
    }

    .card-header,
    .card-title {
      font-weight: bold;
      margin: 0;
    }

    .card-text {
      display: flex;
      flex-direction: column;

      .community-overview__description {
        flex-grow: 1;
        max-height: 120px;
        margin: 0;
      }
    }

    &.hidden {
      opacity: 0;
      pointer-events: none;
    }

    &.visible {
      opacity: 1;
      pointer-events: normal;
    }
  }
}
</style>
