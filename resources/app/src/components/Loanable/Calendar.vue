<template>
  <vue-cal
    :class="classList"
    :disable-views="['years', 'year']"
    :defaultView="defaultView"
    :time-step="15"
    :time-cell-height="18"
    :events="events"
    locale="fr"
    :xsmall="variant == 'small'"
    @ready="$emit('ready', $event)"
    @view-change="$emit('view-change', $event)"
  >
    <template #cell-content="{ cell, view, events }">
      <span class="vuecal__cell-date">
        {{ cell.content }}
      </span>
    </template>

    <template v-slot:time-cell="{ hours, minutes }">
      <div
        :class="{
          'loanable-calendar__time-step--hours': !minutes,
          'loanable-calendar__time-step--minutes': minutes,
        }"
      >
        <span class="line"></span>
        <span v-if="!minutes">{{ hours }}</span>
        <span v-else>{{ minutes }}</span>
      </div>
    </template>
  </vue-cal>
</template>

<script>
import VueCal from "vue-cal";
import "vue-cal/dist/i18n/fr";

export default {
  name: "Calendar",
  props: {
    defaultView: {
      type: String,
      default: "week",
    },
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

  // Month view.
  .vuecal__cells.month-view .vuecal__cell {
    height: 2rem;
  }

  // Styling the time axis.
  // Specificity seems necessary here.
  .vuecal__time-column .loanable-calendar__time-step--hours .line::before {
    border-color: $locomotion-green;
  }
  .loanable-calendar__time-step--hours {
    color: $locomotion-green;
    font-weight: bold;
  }
  .loanable-calendar__time-step--minutes {
    font-size: 0.7em;
  }
}
</style>
