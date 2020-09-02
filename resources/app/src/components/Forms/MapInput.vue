<template>
  <div :class="`forms-map-input ${stateClass}`">
    <gmap-map class="forms-map-input__map" ref="map"
      :center="markerPositionOrCenter" :zoom="14" :options="mapOptions"
      map-type-id="terrain" @click="savePosition">
      <gmap-marker v-if="markerPosition"
        :clickable="false"
        :position="markerPosition" />
      <gmap-polygon v-for="p in polygons" :key="`polygon-${p.id}`"
        :path="p.area_google"
        :label="p.name"
        :options="p.options" />
    </gmap-map>
    <p v-if="description">{{ description }}</p>
  </div>
</template>

<script>
import { gmapApi } from 'vue2-google-maps';

export default {
  name: 'FormsMapInput',
  props: {
    bounded: {
      type: Boolean,
      required: false,
      default: false,
    },
    center: {
      type: Object,
      required: false,
    },
    description: {
      type: String,
      required: false,
    },
    disabled: {
      type: Boolean,
      required: false,
      default: false,
    },
    state: {
      type: Boolean,
      required: false,
      default: true,
    },
    polygons: {
      type: Array,
      required: false,
      default() { return []; },
    },
    value: {
      required: true,
      type: Array,
    },
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
    };
  },
  computed: {
    google: gmapApi,
    markerPosition() {
      if (this.value.length === 2) {
        return {
          lat: this.value[0],
          lng: this.value[1],
        };
      }

      return null;
    },
    markerPositionOrCenter() {
      return this.markerPosition || this.center || { lat: 45.5342925, lng: -73.599039 };
    },
    stateClass() {
      if (this.state === null) {
        return '';
      }

      if (this.state) {
        return 'is-valid';
      }

      return 'is-invalid';
    },
  },
  methods: {
    savePosition(event) {
      if (!this.disabled) {
        const lat = event.latLng.lat();
        const lng = event.latLng.lng();

        if (this.bounded && this.polygons.length > 0) {
          const { Polygon } = this.google.maps;

          for (let i = 0, len = this.polygons.length; i < len; i += 1) {
            const p = new Polygon({ paths: this.polygons[i].area_google });

            if (this.google.maps.geometry.poly.containsLocation(event.latLng, p)) {
              this.$emit('input', [lat, lng]);
              return;
            }

            return;
          }
        }

        this.$emit('input', [lat, lng]);
      }
    },
  },
};
</script>

<style lang="scss">
.forms-map-input {
  border-radius: 0.25rem;

  &__map {
    height: 240px;
  }

  &.is-invalid {
    border: 1px solid $danger;
  }
}
</style>
