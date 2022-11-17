<template>
  <vue-cal
    :class="classList"
    :events="events"
    locale="fr"
    :xsmall="variant == 'small'"
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
    variant: {
      type: String,
      required: false,
    },
  },
  components: { VueCal },
  computed: {
    classList: function () {
      let classList = {
        "loanable-calendar": true,
      };
      if (this.variant) {
        classList["loanable-calendar--" + this.variant] = true;
      }
      return classList;
    },
  },
};
</script>

<style lang="scss">
.loanable-calendar {
  &.loanable-calendar--small {
  }
}
</style>
