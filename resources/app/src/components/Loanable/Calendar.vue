<template>
  <vue-cal
    :events="events"
    locale="fr"
    @ready="$emit('ready', $event)"
    @view-change="$emit('view-change', $event)"
  >
  </vue-cal>
</template>

<script>
import VueCal from "vue-cal";
import "vue-cal/dist/i18n/fr";

export default {
  name: "Calendar",
  props: {
    events: {
      type: Array,
    },
  },
  components: { VueCal },
  data() {
    return {
      events: [],
    };
  },
};
</script>
