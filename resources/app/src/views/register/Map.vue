<template>
  <div name="register-map" v-if="routeDataLoaded">
    <b-card class="register-map__community" v-if="community">
      <template v-slot:header>
        <h4 class="card-title">
          {{ `Voisinage ${community ? community.name : ""}` }}
        </h4>
      </template>
      <b-card-text>
        <div class="register-map__community__description">
          <p>Youpi, vous pouvez rejoindre ce voisinage!</p>

          <p v-if="community.description">
            {{ community.description }}
          </p>
        </div>

        <b-form
          class="register-map__community__submit text-center"
          @submit.prevent="joinCommunity"
          @reset.prevent="resetCommunity"
        >
          <b-button type="submit" variant="primary" class="mr-3 mb-3">Rejoindre</b-button>
          <b-button type="reset" variant="warning" class="mb-3">Retour</b-button>
          <br />
          <b-button variant="outline-light" v-b-modal="'borough-difference-modal'">
            Voisinage, quartier: quelle différence?
          </b-button>
        </b-form>
      </b-card-text>
    </b-card>

    <b-card class="register-map__borough" v-else-if="borough">
      <template v-slot:header>
        <h4 class="card-title">
          {{ `Quartier ${borough ? borough.name : ""}` }}
        </h4>
      </template>
      <b-card-text>
        <div class="register-map__borough__description">
          <p>
            Bravo, c'est un premier pas! Vous pourrez utiliser les vélos, remorques et autos
            disponibles pour le quartier.
          </p>

          <p v-if="borough.description">
            {{ borough.description }}
          </p>
        </div>

        <b-form
          class="register-map__borough__submit text-center"
          @submit.prevent="joinBorough"
          @reset.prevent="resetBorough"
        >
          <b-button type="submit" variant="primary" class="mr-3 mb-3">Rejoindre</b-button>
          <b-button type="reset" variant="warning" class="mb-3">Retour</b-button>
          <br />
          <b-button variant="outline-light" v-b-modal="'register-map-modal'">
            Voisinage, quartier: quelle différence?
          </b-button>
        </b-form>
      </b-card-text>
    </b-card>

    <b-card class="register-map__postal_code" v-else-if="postalCodeCenter">
      <template v-slot:header>
        <h4 class="card-title">Oups!</h4>
      </template>

      <b-card-text>
        <p class="text-center">Locomotion n’est pas encore accessible dans votre quartier.</p>

        <p class="text-center">
          Souhaitez-vous être informé-e du développement du projet dans votre quartier?
        </p>

        <mailchimp-newsletter :item="user" @optin="updateOptInNewsletter" />

        <div class="text-center">
          <b-button @click.prevent="resetView" variant="warning">Retour</b-button>
        </div>
      </b-card-text>
    </b-card>

    <b-card title="Rejoindre mes voisin-e-s" class="register-map__form" v-else>
      <layout-loading v-if="postalCodeLoading" />
      <b-card-text v-else>
        <b-form @submit.prevent="searchPostalCode">
          <b-form-group label="Code postal">
            <b-input-group>
              <b-form-input
                type="text"
                required
                placeholder="Code postal"
                v-mask="'A#A #A#'"
                masked
                v-model="postalCode"
                :state="postalCode ? status !== 'ZERO_RESULTS' : null"
              />

              <b-input-group-append>
                <b-button variant="outline-success" type="submit">OK</b-button>
              </b-input-group-append>
            </b-input-group>

            <b-form-invalid-feedback :state="status !== 'ZERO_RESULTS'">
              Code postal non reconnu.
            </b-form-invalid-feedback>
          </b-form-group>
        </b-form>
      </b-card-text>
    </b-card>

    <borough-difference-modal />

    <div v-if="communities">
      <gmap-map
        class="register-map__map"
        ref="map"
        :zoom="zoom"
        :center="center"
        :options="mapOptions"
        map-type-id="terrain"
      >
        <gmap-polygon
          v-for="b in boroughs"
          :key="`polygon-${b.id}`"
          :path="b.area_google"
          :label="b.name"
          :options="boroughPolygonOptions"
          @click="borough = b"
        />
        <gmap-polygon
          v-for="c in communities"
          :key="`polygon-${c.id}`"
          :path="c.area_google"
          :label="c.name"
          :options="polygonOptions"
          @click="
            community = c;
            borough = c.parent;
          "
        />
        <gmap-marker
          v-if="postalCodeCenter"
          :icon="{
            url: '/pins/default-pin.svg',
            scaledSize: {
              width: 28,
              height: 40,
            },
          }"
          :clickable="false"
          :position="postalCodeCenter"
        />
      </gmap-map>
    </div>
  </div>
  <layout-loading v-else />
</template>

<script>
import { gmapApi } from "vue2-google-maps";

import BoroughDifferenceModal from "@/components/Misc/BoroughDifferenceModal.vue";
import MailchimpNewsletter from "@/components/Misc/MailchimpNewsletter.vue";

import Authenticated from "@/mixins/Authenticated";
import DataRouteGuards from "@/mixins/DataRouteGuards";
import FormMixin from "@/mixins/FormMixin";
import UserMixin from "@/mixins/UserMixin";

import { distance } from "@/helpers";

export default {
  name: "Map",
  mixins: [Authenticated, DataRouteGuards, FormMixin, UserMixin],
  props: {
    id: {
      required: false,
      default: "me",
    },
  },
  components: {
    BoroughDifferenceModal,
    MailchimpNewsletter,
  },
  data() {
    return {
      boroughPolygonOptions: {
        fillColor: "#16a59e",
        fillOpacity: 0.3,
        strokeColor: "#16a59e",
        strokeOpacity: 0.7,
        zIndex: 1,
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
      polygonOptions: {
        fillColor: "#16a59e",
        fillOpacity: 0.5,
        strokeOpacity: 0,
        zIndex: 2,
      },
      postalCodeCenter: null,
      postalCodeLoading: false,
      status: null,
      zoom: 1,
    };
  },
  computed: {
    averageCommunitiesCenter() {
      const center = this.communities.reduce(
        (acc, c) => [(acc[0] + c.center[0]) / 2, (acc[1] + c.center[1]) / 2],
        this.communities[0].center
      );
      return {
        lat: center[0],
        lng: center[1],
      };
    },
    borough: {
      get() {
        return this.$store.state["register.map"].borough;
      },
      set(borough) {
        this.$store.commit("register.map/borough", borough);
      },
    },
    boroughs() {
      return this.$store.state.communities.data.filter((c) => c.type === "borough");
    },
    center: {
      get() {
        if (this.$store.state["register.map"].center) {
          return this.$store.state["register.map"].center;
        }

        if (this.community) {
          return {
            ...this.community.center_google,
            lng: this.community.center_google.lng - 0.007,
          };
        }

        if (this.borough) {
          return {
            ...this.borough.center_google,
            lng: this.borough.center_google.lng - 0.007,
          };
        }

        return {
          ...this.averageCommunitiesCenter,
          lng: this.averageCommunitiesCenter.lng - 0.007,
        };
      },
      set(center) {
        this.$store.commit("register.map/center", center);
      },
    },
    community: {
      get() {
        return this.$store.state["register.map"].community;
      },
      set(community) {
        this.$store.commit("register.map/community", community);
      },
    },
    communities() {
      if (!this.$store.state.communities.data) {
        return [];
      }

      return this.$store.state.communities.data.filter((c) => c.type === "neighborhood");
    },
    google: gmapApi,
    paths() {
      return this.communities.map((c) => c);
    },
    postalCode: {
      get() {
        return this.$store.state["register.map"].postalCode;
      },
      set(value) {
        this.$store.commit("register.map/postalCode", value);
      },
    },
  },
  methods: {
    centerOnCommunity(community) {
      const { LatLngBounds } = this.google.maps;
      const bounds = new LatLngBounds();

      community.area_google.forEach((p) => bounds.extend(p));

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
    distance,
    async joinBorough() {
      await this.$store.dispatch("users/joinCommunity", {
        userId: this.$store.state.user.id,
        communityId: this.borough.id,
      });

      this.$store.commit("user", this.item);

      this.$router.push("/register/3");
    },
    async joinCommunity() {
      await this.$store.dispatch("users/joinCommunity", {
        userId: this.$store.state.user.id,
        communityId: this.community.id,
      });

      this.$store.commit("user", this.item);

      this.$router.push("/register/3");
    },
    resetCenter() {
      const { LatLngBounds } = this.google.maps;
      const bounds = new LatLngBounds();

      this.communities.forEach((c) => bounds.extend(c.center_google));
      this.$refs.map.fitBounds(bounds);
    },
    resetBorough() {
      this.borough = null;
      this.postalCodeCenter = null;
    },
    resetCommunity() {
      this.community = null;
      if (!this.borough) {
        this.postalCodeCenter = null;
      }
    },
    resetView() {
      this.borough = null;
      this.community = null;
      this.postalCodeCenter = null;
      this.postalCodeLoading = false;

      this.resetCenter();
    },
    searchPostalCode() {
      // Don't bother searching for invalid postal code
      if (!this.postalCode.match(/[a-z][0-9][a-z]\s*[0-9][a-z][0-9]/i)) {
        return false;
      }

      this.postalCodeLoading = true;
      this.status = null;
      this.postalCodeCenter = null;

      const { Geocoder, LatLngBounds, Polygon } = this.google.maps;
      const geocoder = new Geocoder();

      geocoder.geocode({ address: this.postalCode }, (results, status) => {
        this.status = status;

        // No results: do nothing
        if (status === "ZERO_RESULTS") {
          this.postalCodeLoading = false;
          return true;
        }

        if (status !== "OK") {
          return false;
        }

        // Results found: work on the first one (most likely candidate)
        const { location } = results[0].geometry;
        this.postalCodeCenter = {
          lat: location.lat(),
          lng: location.lng(),
        };

        // Is it in a community?
        for (let i = 0, lenI = this.communities.length; i < lenI; i += 1) {
          const community = this.communities[i];
          const polygon = new Polygon({ paths: community.area_google });

          if (this.google.maps.geometry.poly.containsLocation(location, polygon)) {
            this.center = null;
            this.community = community;

            this.postalCodeLoading = false;
            return true;
          }
        }

        // Is it in a borough?
        for (let j = 0, lenJ = this.boroughs.length; j < lenJ; j += 1) {
          const borough = this.boroughs[j];
          const polygon = new Polygon({ paths: borough.area_google });

          if (this.google.maps.geometry.poly.containsLocation(location, polygon)) {
            this.center = null;
            this.borough = borough;

            this.postalCodeLoading = false;
            return true;
          }
        }

        // If it's neither in a community or a borough:
        // center the view on marker and nearest borough
        const bounds = new LatLngBounds();
        bounds.extend(location);

        let nearestDistance = this.distance(this.postalCodeCenter, this.boroughs[0].center_google);
        const nearestBorough = this.boroughs.reduce((acc, b) => {
          const boroughdistance = this.distance(
            this.postalCodeCenter,
            this.boroughs[0].center_google
          );
          if (boroughdistance < nearestDistance) {
            nearestDistance = boroughdistance;
            return b;
          }

          return acc;
        }, this.boroughs[0]);

        bounds.extend(nearestBorough.center_google);
        bounds.extend({
          ...nearestBorough.center_google,
          lng: nearestBorough.center_google.lng + 0.007,
        });

        this.$refs.map.fitBounds(bounds);

        this.postalCodeLoading = false;
        return true;
      });

      return true;
    },
    async updateOptInNewsletter(value) {
      await this.$store.dispatch("users/update", {
        id: this.user.id,
        data: {
          opt_in_newsletter: value,
        },
        params: {
          fields: "id,name,opt_in_newsletter",
        },
      });
    },
  },
  watch: {
    routeDataLoaded() {
      setTimeout(() => {
        this.$refs.map.$mapPromise.then(() => {
          if (!this.community) {
            this.resetCenter();
          } else {
            this.centerOnCommunity(this.community);
          }

          if (this.postalCode) {
            this.searchPostalCode();
          }
        });
      }, 500);
    },
    community(value) {
      if (!value) {
        return this.resetCenter();
      }

      this.postalCode = "";
      return this.centerOnCommunity(value);
    },
    "item.postal_code": function syncPostalCode(val) {
      this.$store.commit("register.map/postalCode", val);
    },
  },
};
</script>

<style lang="scss">
@import "~bootstrap/scss/mixins/breakpoints";

.register-map {
  position: relative;

  &__map {
    width: 100%;
    height: calc(100vh - #{$layout-navbar-height} - 1px);
    z-index: 10;

    @include media-breakpoint-down(lg) {
      min-height: calc(100vh - #{$layout-navbar-height-mobile} - 1px);
    }
  }

  &__form.card {
    margin-top: 50px;
    margin-left: 30px;
    position: absolute;
    z-index: 100;
  }

  &__community.card,
  &__borough.card,
  &__postal_code.card {
    margin-top: 50px;
    margin-left: 30px;
    position: absolute;
    z-index: 100;
    max-width: 500px;

    .card-header,
    .card-header .card-title {
      margin-bottom: 0;
      font-weight: bold;
    }

    .card-text {
      display: flex;
      flex-direction: column;

      .register-map__community__description {
        flex-grow: 1;
        max-height: 120px;
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

  &__community__buttons {
    margin-bottom: 10px;
  }

  &__community__buttons,
  &__community__submit {
    text-align: left;
  }
}
</style>
