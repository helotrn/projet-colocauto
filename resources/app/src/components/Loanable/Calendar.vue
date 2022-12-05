<template>
  <vue-cal
    :class="classList"
    :hide-view-selector="true"
    :disable-views="['years', 'year']"
    :defaultView="defaultView"
    :time-step="15"
    :time-cell-height="variant == 'small' ? 13 : 18"
    :events="vueCalEvents"
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
    vueCalEvents: function () {
      let baseEvent = {
        deletable: false,
        resizable: false,
        draggable: false,
        class: ["loanable-calendar__event"],
      };

      let vueCalEvents = this.events.map((e) => {
        e = { ...baseEvent, ...e };

        if (e.type == "availability") {
          // Availability events go in the background.
          e.background = true;
          if (e.data.available) {
            e.class.push("loanable-calendar__event--availability");
          } else {
            e.class.push("loanable-calendar__event--unavailability");
          }
        } else if (e.type == "loan") {
          // Loans don't go in the background.
          e.background = false;

          // Class based on loan status.
          e.class.push("loanable-calendar__event--loan_" + e.data.status);
        }

        // Pass class as a string to vue-cal.
        e.class = e.class.join(" ");

        return e;
      });

      return vueCalEvents;
    },
  },
};
</script>

<style lang="scss">
.loanable-calendar {
  &.loanable-calendar--small {
    font-size: 13px;
    .vuecal__title-bar {
      font-size: 13px;
    }
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

  .loanable-calendar__event {
    opacity: 0.8;
  }
  .loanable-calendar__event.vuecal__event--background {
    // Leave background events in the background.
    z-index: 0;
  }
  .loanable-calendar__event:not(.vuecal__event--background) {
    z-index: 3;
  }
  .loanable-calendar__event--availability {
    background-color: $success;
  }
  .loanable-calendar__event--unavailability {
    background-color: $danger;
  }
  .loanable-calendar__event--loan_in_process {
    background-color: $warning;
    border: 1px solid $warning;
  }
  .loanable-calendar__event--loan_completed {
    color: $success;
    background-color: $success;
    border: 1px solid $success;
  }
  .loanable-calendar__event--loan_canceled {
    color: $danger;
    background-color: $danger;
    border: 1px solid $danger;
  }
}
</style>
