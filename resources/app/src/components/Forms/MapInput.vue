<template>
  <div :class="`forms-map-input ${stateClass}`">
    <gmap-map class="forms-map-input__map" ref="map"
      :center="markerPositionOrCenter" :zoom="14" :options="mapOptions"
      map-type-id="terrain" @click="savePosition">
      <gmap-marker v-if="markerPosition"
        :clickable="false"
        :position="markerPosition" />
    </gmap-map>
    <p v-if="description">{{ description }}</p>
  </div>
</template>

<script>
export default {
  name: 'FormsMapInput',
  props: {
    center: {
      type: Object,
      required: false,
    },
    description: {
      type: String,
      required: false,
    },
    state: {
      type: Boolean,
      required: false,
      default: true,
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
      return this.markerPosition || this.center || { lat: 0, lng: 0 };
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
      const lat = event.latLng.lat();
      const lng = event.latLng.lng();
      this.$emit('input', [lat, lng]);
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
