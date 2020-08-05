<template>
  <gmap-map class="community-map"
    ref="map"
    :center="center"
    :zoom="14"
    :options="mapOptions"
    @click="selectedLoanable = null"
    map-type-id="terrain">
    <gmap-polygon v-for="c in communities" :key="`polygon-${c.id}`"
      :path="c.area_google"
      :label="c.name"
      :options="polygonOptions" />
    <gmap-marker v-for="l in data" :key="`marker-${l.id}`"
      :icon="iconFor(l)" @click="activateLoanable(l)"
      :clickable="l.available !== false" :position="l.position_google">
      <gmap-info-window v-if="selectedLoanable && selectedLoanable.id === l.id"
        @closeclick="selectedLoanable = null">
        <div class="info-box-content" style="width: 270px;">
          <loanable-card v-bind="l" @select="$emit('select', l)" @test="$emit('test', l)" />
        </div>
      </gmap-info-window>
    </gmap-marker>
  </gmap-map>
</template>

<script>
import { gmapApi } from 'vue2-google-maps';

import LoanableCard from '@/components/Loanable/Card.vue';

export default {
  name: 'CommunityMap',
  components: {
    LoanableCard,
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
      selectedLoanable: null,
    };
  },
  computed: {
    averageCommunitiesCenter() {
      const center = this.communities.reduce((acc, c) => [
        (acc[0] + c.center[0]) / 2,
        (acc[1] + c.center[1]) / 2,
      ], this.communities[0].center);
      return {
        lat: center[0],
        lng: center[1],
      };
    },
    center: {
      get() {
        if (this.$store.state['community.view'].center) {
          return this.$store.state['community.view'].center;
        }

        return this.averageCommunitiesCenter;
      },
      set(center) {
        this.$store.commit('community.view/center', center);
      },
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
    iconFor(loanable) {
      const status = loanable.available === false ? '-unavailable-' : '-';

      let url;
      switch (loanable.type) {
        case 'car':
        case 'bike':
        case 'trailer':
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
        return 'unavailable';
      }

      return '';
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

  .loanable-card.card {
    padding: 0;
  }
}
</style>
