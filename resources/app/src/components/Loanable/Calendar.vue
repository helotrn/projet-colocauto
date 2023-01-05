<template>
  <vue-cal
    :class="classList"
    :hide-view-selector="true"
    :disable-views="['years', 'year']"
    :defaultView="defaultView"
    :time-step="variant === 'small' ? 60 : 15"
    :time-cell-height="variant === 'small' ? 24 : 18"
    :events="vueCalEvents"
    locale="fr"
    start-week-on-sunday
    :xsmall="variant === 'small'"
    @ready="$emit('ready', $event)"
    @view-change="$emit('view-change', $event)"
  >
    <template v-slot:title="{ title, view }">
      <span v-if="view.id === 'years'">Years</span>
      <span v-else-if="view.id === 'year'">{{ view.startDate.format("YYYY") }}</span>
      <span v-else-if="view.id === 'month'">{{ view.startDate.format("MMMM YYYY") }}</span>
      <span v-else-if="view.id === 'week'">{{ view.startDate.format("MMMM YYYY") }}</span>
      <span v-else-if="view.id === 'day'">{{ view.startDate.format("dddd D MMMM (YYYY)") }}</span>
    </template>

    <template v-slot:time-cell="{ hours, minutes }">
      <div
        :class="{
          'loanable-calendar__time-step--hours': !minutes,
          'loanable-calendar__time-step--minutes': minutes,
        }"
      >
        <span class="line"></span>
        <template v-if="variant === 'small'">
          <span
            >{{ hours < 10 ? "0" + hours : hours }}:{{
              minutes < 10 ? "0" + minutes : minutes
            }}</span
          >
        </template>
        <template v-else>
          <span v-if="!minutes">{{ hours }}</span>
          <span v-else>{{ minutes }}</span>
        </template>
      </div>
    </template>

    <template #cell-content="{ cell, view, events }">
      <span
        v-if="view.id === 'month'"
        :class="`vuecal__cell-date ${getMonthDayAvailabilityClass(events, cell)}`"
      >
        {{ cell.content }}
      </span>
      <span v-else class="vuecal__cell-date">
        {{ cell.content }}
      </span>
    </template>

    <template v-slot:event="{ event, view }">
      <div class="vuecal__event-title" v-html="event.title" />
      <!-- Or if your events are editable: -->
      <div
        class="vuecal__event-title vuecal__event-title--edit"
        contenteditable
        @blur="event.title = $event.target.innerHTML"
        v-html="event.title"
      />
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

        if (e.type === "availability") {
          // Availability events go in the background.
          e.background = true;
          if (e.data.available) {
            e.class.push("loanable-calendar__event--availability");
          } else {
            e.class.push("loanable-calendar__event--unavailability");
          }
        } else if (e.type === "loan") {
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
  methods: {
    getMonthDayAvailabilityClass(events, cell) {
      const today = this.$dayjs().startOfDay();

      const cellStartTime = this.$dayjs(cell.startDate).startOfDay();
      const cellEndTime = cellStartTime.add(1, "day");

      let eventStartTime, eventEndTime;

      let availabilityClass = "vuecal__cell--available";

      let nEvents = events.length;

      // Month-view events are events of unavailability.
      for (let e = 0; e < nEvents; e++) {
        eventStartTime = this.$dayjs(events[e].start);
        eventEndTime = this.$dayjs(events[e].end);

        if (
          0 == eventStartTime.diff(cellStartTime, "seconds") &&
          0 == eventEndTime.diff(cellEndTime, "seconds")
        ) {
          // If event spans the whole day, then unavailable.
          availabilityClass = "vuecal__cell--unavailable";
        } else {
          // If event does not span the whole day, then partially available.
          availabilityClass = "vuecal__cell--partially-available";
        }
      }

      return availabilityClass;
    },
  },
};
</script>

<style lang="scss">
.loanable-calendar {
  &.vuecal--xsmall {
    font-size: 13px;
    .vuecal__title-bar,
    .vuecal__weekdays-headings {
      background-color: transparent;
      // Needs to be repeated for title-bar to overload .vuecal--xsmall .vuecal__title-bar
      font-size: 13px;
    }
    .vuecal__heading {
      height: inherit;
      min-height: 2em;
      line-height: 1.3;
    }
  }

  // Month view.
  .vuecal__cells.month-view .vuecal__cell {
    height: 2rem;
  }
  .vuecal__cells.month-view .vuecal__cell::before {
    border: none;
  }
  .vuecal__cells.month-view .vuecal__cell--available {
    color: $success;
  }
  .vuecal__cells.month-view .vuecal__cell--partially-available {
    color: $warning;
  }
  .vuecal__cells.month-view .vuecal__cell--unavailable {
    color: $danger;
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
