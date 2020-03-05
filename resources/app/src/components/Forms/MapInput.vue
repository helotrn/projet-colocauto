<template>
  <div class="forms-map-input">
    <gmap-map class="forms-map-input__map" ref="map"
      :center="center || markerPosition" :zoom="14" :options="mapOptions"
      map-type-id="terrain" @click="savePosition">
      <gmap-marker
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
      return {
        lat: this.value[0],
        lng: this.value[1],
      };
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
.forms-map-input__map {
  height: 240px;
}
</style>
