<template>
  <layout-page name="registration-map" wide>
    <b-card title="Trouver une communauté" class="registration-map__form" v-if="!community">
      <b-card-text>
        <b-form @submit.prevent="searchPostalCode">
          <b-form-group label="Code postal">
            <b-form-input type="text" required placeholder="Code postal"
              v-model="postalCode" />
          </b-form-group>
        </b-form>
      </b-card-text>
    </b-card>

    <b-card class="registration-map__community" v-if="community">
      <template v-slot:header>
        <div class="registration-map__community__buttons">
          <b-button @click="previousCommunity">Précédente</b-button>
          <b-button @click="nextCommunity">Suivante</b-button>
        </div>

        <h4 class="card-title">
          {{ `Rejoindre la communauté ${community ? community.name : ''}` }}
        </h4>
      </template>
      <b-card-text>
        <div class="registration-map__community__description">
          <p v-if="community">
            {{ community.description }}
          </p>
        </div>

        <b-form class="registration-map__community__buttons"
          @submit.prevent="completeRegistration" @reset.prevent="resetCommunity">
          <b-button type="submit" variant="primary">Poursuivre l'inscription</b-button>
          <b-button type="reset" variant="warning">Revenir aux communautés</b-button>
        </b-form>
      </b-card-text>
    </b-card>

    <gmap-map class="registration-map__map"
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
        :label="zoom > 14 ? {
          text: c.name,
          color: '#ffffff',
          fontWeight: '600',
          fontFamily: 'BrandonText',
          fontSize: '40px'
        } : null"
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
  mounted() {
    this.$refs.map.$mapPromise.then(() => {
      if (!this.community) {
        this.resetCenter();
      } else {
        this.centerOnCommunity(this.community);
      }
    });
  },
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
    averageCommunitiesCenter() {
      const { data: communities } = this.$store.state.communities;
      const center = communities.reduce((acc, c) => [
        (acc[0] + c.center[0]) / 2,
        (acc[1] + c.center[1]) / 2,
      ], communities[0].center);
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
      },
    },
  },
  methods: {
    centerOnCommunity(community) {
      const { LatLngBounds } = this.google.maps;
      const bounds = new LatLngBounds();

      community.area_google.forEach(p => bounds.extend(p));
      this.$refs.map.fitBounds(bounds);
    },
    nextCommunity() {
      const index = this.communities.indexOf(this.community);

      if (index + 1 >= this.communities.length) {
        this.community = this.communities[0];
      } else {
        this.community = this.communities[index + 1];
      }
    },
    previousCommunity() {
      const index = this.communities.indexOf(this.community);

      if (index - 1 < 0) {
        this.community = this.communities[this.communities.length - 1];
      } else {
        this.community = this.communities[index - 1];
      }
    },
    resetCenter() {
      const { LatLngBounds } = this.google.maps;
      const bounds = new LatLngBounds();

      this.communities.forEach(c => bounds.extend(c.center_google));
      this.$refs.map.fitBounds(bounds);
    },
    resetCommunity() {
      this.community = null;
    },
    searchPostalCode() {
      const { Geocoder, LatLngBounds } = this.google.maps;
      const geocoder = new Geocoder();

      geocoder.geocode({ address: this.postalCode }, (results, status) => {
        if (status === 'OK' && results.length > 0) {
          const { location } = results[0].geometry;

          for (let i = 0, len = this.communities.length; i < len; i += 1) {
            const community = this.communities[i];
            const bounds = new LatLngBounds();

            community.area_google.forEach(p => bounds.extend(p));
            if (bounds.contains(location)) {
              this.center = null;
              this.community = community;
              return true;
            }
          }
        }

        const bounds = new LatLngBounds();
        bounds.extend(results[0].geometry.location);
        const kmAway = {
          lat: results[0].geometry.location.lat(),
          lng: results[0].geometry.location.lng(),
        };
        kmAway.lat += 0.003;
        bounds.extend(kmAway);
        this.$refs.map.fitBounds(bounds);

        return true;
      });
    },
  },
  watch: {
    community(value) {
      if (!value) {
        return this.resetCenter();
      }

      this.postalCode = '';
      return this.centerOnCommunity(value);
    },
    postalCode(val) {
      if (val.match(/[a-z][0-9][a-z]\s*[0-9][a-z][0-9]/i)) {
        this.searchPostalCode();
      }

      this.resetCenter();
    },
  },
};
</script>

<style lang="scss">
.registration-map.page {
  .registration-map {
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
      margin-left: 30px;
      position: absolute;
      z-index: 100;
      max-width: 50vw;

      .card-header {
        margin-bottom: 0;
      }

      .card-text {
        display: flex;
        flex-direction: column;

        .registration-map__community__description {
          flex-grow: 1;
          max-height: 120px;
        }
      }

      &__buttons {
        text-align: left;

        .btn {
          margin-right: 10px;
          margin-bottom: 10px;
        }

        .btn:last-child {
          margin-right: 0;
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
}
</style>
