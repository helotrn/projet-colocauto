<template>
  <layout-page name="registration-map" wide>
    <b-card title="Trouver une communauté" class="registration-map-page__form">
      <b-card-text>
        <b-form @submit.prevent="searchPostalCode">
          <b-form-group label="Code postal">
            <b-form-input type="text" required placeholder="Code postal"
              v-model="postalCode" />
          </b-form-group>

          <b-button type="submit" variant="primary">Rechercher</b-button>
        </b-form>
      </b-card-text>
    </b-card>

    <b-card :title="`Rejoindre la communauté ${community ? community.name : ''}`"
      :class="`registration-map-page__community ${community ? 'visible' : 'hidden'}`">
      <b-card-text>
        <div class="registration-map-page__community__description">
          <p v-if="community">
            {{ community.description }}
          </p>
        </div>

        <b-form @submit.prevent="completeRegistration" @reset.prevent="resetPostalCode">
          <b-button type="submit" variant="primary">Poursuivre l'inscription</b-button><br>
          <b-button type="reset" variant="warning">Revenir aux communautés</b-button>
        </b-form>
      </b-card-text>
    </b-card>

    <gmap-map class="registration-map-page__map"
      ref="map"
      :center="center"
      :zoom="14"
      :options="{
        streetViewControl: false,
        mapTypeControl: false,
        styles: [
          {
            featureType: 'poi',
            stylers: [
              { visibility: 'off' }
            ]
          }
        ]
      }"
      map-type-id="terrain">
      <gmap-polygon v-for="c in communities" :key="`polygon-${c.id}`"
        :path="c.area_google"
        :label="c.name"
        :options="{ fillColor: '#16a59e', fillOpacity: 0.5, strokeOpacity: 0, label: 'machin' }"
        @click="community = c" />
      <gmap-marker v-for="c in communities" :key="`marker-${c.id}`"
        :label="{
          text: c.name,
          color: '#ffffff',
          fontWeight: '600',
          fontFamily: 'BrandonText',
          fontSize: '40px'
        }"
        :clickable="false"
        :icon="mapIcon"
        :position="c.center_google" />
    </gmap-map>
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
    };
  },
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
    center: {
      get() {
        if (this.$store.state['registration.map'].center) {
          return this.$store.state['registration.map'].center;
        }

        if (this.community) {
          return this.community.center_google;
        }

        return this.averageCommunitiesCenter;
      },
      set(center) {
        this.$store.commit('registration.map/center', center);
      },
    },
    community: {
      get() {
        return this.$store.state['registration.map'].community;
      },
      set(community) {
        this.$store.commit('registration.map/community', community);
      },
    },
    communities() {
      return this.$store.state.communities.data;
    },
    google: gmapApi,
    paths() {
      return this.communities.map(c => c);
    },
    postalCode: {
      get() {
        return this.$store.state['registration.map'].postalCode;
      },
      set(value) {
        this.$store.commit('registration.map/postalCode', value);
        this.center = null;
      },
    },
  },
  methods: {
    resetPostalCode() {
      this.center = null;
      this.community = null;
    },
    searchPostalCode() {
      const { Geocoder } = this.google.maps;
      const geocoder = new Geocoder();

      geocoder.geocode({ address: this.postalCode }, (results, status) => {
        if (status === 'OK' && results.length > 0) {
          const { location } = results[0].geometry;
          const { LatLngBounds } = this.google.maps;
          const bounds = new LatLngBounds();

          for (let i = 0, len = this.communities.length; i < len; i++) {
            const community = this.communities[i];
            community.area_google.forEach(p => bounds.extend(p));
            if (bounds.contains(location)) {
              this.community = community;
              this.center = null;
              return true;
            }
          }

          this.center = results[0].geometry.location;
          this.community = null;
        }
      });
    },
  },
  watch: {
    community(value) {
      const { LatLngBounds } = this.google.maps;
      const bounds = new LatLngBounds();

      if (!value) {
          this.communities.forEach(c => bounds.extend(c.center_google));
          this.$refs.map.fitBounds(bounds);
      } else {
        value.area_google.forEach(p => bounds.extend(p));
        this.$refs.map.fitBounds(bounds);
      }
    },
  },
};
</script>

<style lang="scss">
.registration-map.page {
  .registration-map-page {
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

    &__community {
      margin-top: 50px;
      margin-right: 30px;
      right: 0;
      position: absolute;
      z-index: 100;

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
}
</style>
