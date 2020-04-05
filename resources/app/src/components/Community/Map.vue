<template>
  <gmap-map class="community-map"
    ref="map"
    :center="center"
    :zoom="14"
    :options="mapOptions"
    @click="selectedLoanable = null"
    map-type-id="terrain">
    <gmap-marker v-for="l in data" :key="`marker-${l.id}`"
      :icon="iconFor(l)" @click="selectedLoanable = l"
      :clickable="l.available !== false" :position="l.position_google">
      <gmap-info-window v-if="selectedLoanable === l" @closeclick="selectLoanable = null">
        <div style="width: 270px;">
          <loanable-card v-bind="l" @select="emitSelect(l)" />
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
    emitSelect(loanable) {
      this.$emit('select', loanable);
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
