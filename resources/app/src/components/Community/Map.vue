<template>
  <gmap-map
    class="community-map"
    ref="map"
    :center="center"
    :zoom="14"
    :options="mapOptions"
    @click="selectedLoanable = null"
    map-type-id="terrain"
  >
    <gmap-polygon
      v-for="c in communitiesWithArea"
      :key="`polygon-${c.id}`"
      :path="c.area_google"
      :label="c.name"
      :options="polygonOptions"
    />
    <gmap-marker
      v-for="l in data"
      :key="`marker-${l.id}`"
      :icon="iconFor(l)"
      @click="activateLoanable(l)"
      :clickable="l.available !== false"
      :position="l.position_google"
    >
      <gmap-info-window
        v-if="selectedLoanable && selectedLoanable.id === l.id"
        @closeclick="selectedLoanable = null"
      >
        <loanable-details :loanable="l" />
      </gmap-info-window>
    </gmap-marker>
  </gmap-map>
</template>

<script>
import { gmapApi } from "vue2-google-maps";

import LoanableCard from "@/components/Loanable/Card.vue";

export default {
  name: "CommunityMap",
  components: {
    LoanableCard,
  },
  mounted() {
    setTimeout(() => {
      this.$refs.map.$mapPromise.then(() => {
        this.centerOnCommunities();
      });
    }, 500);
  },
  props: {
    data: {
      type: Array,
      required: true,
    },
    communities: {
      type: Array,
      required: true,
    },
  },
  data() {
    return {
      mapOptions: {
        clickableIcons: false,
        fullscreenControl: false,
        mapTypeControl: false,
        streetViewControl: false,
        styles: [
          {
            featureType: "poi",
            stylers: [{ visibility: "off" }],
          },
        ],
      },
      polygonOptions: {
        fillColor: "#16a59e",
        fillOpacity: 0.25,
        strokeOpacity: 0,
        zIndex: 2,
      },
      selectedLoanable: null,
    };
  },
  computed: {
    defaultCenter() {
      return { lat: 45.53748, lng: -73.60145 };
    },
    center: {
      get() {
        if (this.$store.state["community.view"].center) {
          return this.$store.state["community.view"].center;
        }

        return this.defaultCenter;
      },
      set(center) {
        this.$store.commit("community.view/center", center);
      },
    },
    communitiesWithArea() {
      return (this.communities || []).filter((c) => !!c.area);
    },
    google: gmapApi,
  },
  methods: {
    activateLoanable(l) {
      if (this.selectedLoanable === l) {
        this.selectedLoanable = null;
      } else {
        this.selectedLoanable = l;
      }
    },
    centerOnCommunities() {
      const { LatLngBounds } = this.google.maps;
      const bounds = new LatLngBounds();
      this.communitiesWithArea.forEach((c) => c.area_google.forEach((p) => bounds.extend(p)));

      if (document.body.clientWidth >= 992) {
        const sw = bounds.getSouthWest();
        const ne = bounds.getNorthEast();
        const leftPad = {
          lat: sw.lat(),
          lng: sw.lng() + (sw.lng() - ne.lng()),
        };
        bounds.extend(leftPad);
      }

      this.$refs.map.fitBounds(bounds);
    },
    iconFor(loanable) {
      const status = loanable.available === false ? "-unavailable-" : "-";

      let url;
      switch (loanable.type) {
        case "car":
        case "bike":
        case "trailer":
          url = `/pins/${loanable.type}${status}pin.svg`;
          break;
        default:
          return {
            url: `/pins/default${status}pin.svg`,
            scaledSize: {
              width: 28,
              height: 40,
            },
          };
      }

      return {
        url,
        scaledSize: {
          width: 28,
          height: 40,
        },
      };
    },
    markerClasses(loanable) {
      if (loanable.available === false) {
        return "unavailable";
      }

      return "";
    },
  },
  watch: {
    selectedLoanable(newValue, oldValue) {
      if (newValue && newValue !== oldValue) {
        this.center = newValue.position_google;
      }
    },
  },
};
</script>

<style lang="scss">
.community-map {
  .gm-style-iw-d {
    overflow-x: hidden;
  }

  // Adapt Google InfoWindow to the loanable details.
  .gm-style-iw-c {
    padding: 0 !important;

    .gm-style-iw-d {
      max-height: none !important;

      // Main content of loanable details will scroll
      overflow: hidden;
    }
  }
}
</style>
