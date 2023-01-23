<template>
  <vue-cal
    :class="classList"
    :hide-view-selector="true"
    :disable-views="['years', 'year']"
    :defaultView="defaultView"
    :time-step="60"
    :time-cell-height="variant === 'small' ? 24 : 32"
    :events="vueCalEvents"
    locale="fr"
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
        <template v-if="!minutes">
          <span
            >{{ hours < 10 ? "0" + hours : hours }}:{{
              minutes < 10 ? "0" + minutes : minutes
            }}</span
          >
        </template>
      </div>
    </template>

    <template #cell-content="{ cell, view, events }">
      <calendar-month-cell-content
        v-if="view.id === 'month'"
        :availability="getMonthDayAvailability(events, cell)"
        :current="cell.today"
      >
        {{ cell.content }}
      </calendar-month-cell-content>

      <span v-else class="vuecal__cell-date">
        {{ cell.content }}
      </span>
    </template>

    <template v-slot:event="{ event, view }">
      <div class="vuecal__event-title" v-html="event.title" />
    </template>
  </vue-cal>
</template>

<script>
import VueCal from "vue-cal";

import CalendarMonthCellContent from "@/components/Loanable/CalendarMonthCellContent.vue";

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
  components: {
    VueCal,
    "calendar-month-cell-content": CalendarMonthCellContent,
  },
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
    getMonthDayAvailability(events, cell) {
      const cellStartTime = this.$dayjs(cell.startDate).startOfDay();
      const cellEndTime = cellStartTime.add(1, "day");

      let eventStartTime, eventEndTime;

      let availability = "available";

      let nEvents = events.length;

      // Month-view events are events of unavailability.
      for (let e = 0; e < nEvents; e++) {
        eventStartTime = this.$dayjs(events[e].start);
        eventEndTime = this.$dayjs(events[e].end);

        if( this.$dayjs().diff(cellEndTime) > 0 ) {
          availability = "unavailable";
        } else if (
          0 == eventStartTime.diff(cellStartTime, "seconds") &&
          0 == eventEndTime.diff(cellEndTime, "seconds")
        ) {
          // If event spans the whole day, then unavailable.
          availability = events[e].data.available ? "available" : "unavailable";
        } else {
          // If event does not span the whole day, then partially available.
          availability = "partially-available";
        }
      }

      return availability;
    },
  },
};
</script>

<style lang="scss">
.loanable-calendar {
  font-variant-numeric: tabular-nums;

  &.loanable-calendar--small {
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

  .vuecal__cell--selected {
    background-color: transparent;
  }
  .vuecal__cell--current,
  .vuecal__cell--today {
    background-color: rgba(240, 240, 255, 0.4);
  }
  .vuecal__event--focus,
  .vuecal__event:focus {
    box-shadow: none;
  }

  // Month view.
  .vuecal__cells.month-view {
    .vuecal__cell {
      height: 3rem;

      &::before {
        border: none;
      }
    }

    .vuecal__cell--today {
      background-color: transparent;

      .loanable-calendar__cell-date-background svg {
        stroke: currentColor;
      }
      .loanable-calendar__cell-date--unavailable svg rect {
        fill: $beige;
      }
    }
  }

  &.loanable-calendar--small .vuecal__cells.month-view .vuecal__cell {
    height: 2rem;
  }

  .vuecal__cell--out-of-scope {
    // Disabled buttons have opacity: 0.65, but it does not feel clear enough here, hence 0.4.
    opacity: 0.4;
  }

  .loanable-calendar__cell-date {
    position: relative;
    width: 100%;
    height: 100%;

    .loanable-calendar__cell-date-background {
      position: absolute;

      top: 0;
      left: 0;

      height: 100%;
      width: 100%;

      svg {
        height: 100%;
        width: 100%;
      }
    }

    .loanable-calendar__cell-date-content {
      position: absolute;
      top: 0;
      left: 0;

      height: 100%;
      width: 100%;

      display: flex;
      flex-direction: column;
      flex: 1 1 auto;
      justify-content: center;
    }

    &.loanable-calendar__cell-date--available {
      color: $content-alert-positive;

      svg rect {
        fill: $background-alert-positive;
      }
    }
    &.loanable-calendar__cell-date--partially-available {
      color: $content-alert-warning;

      svg path {
        fill: $background-alert-warning;
      }
    }
    &.loanable-calendar__cell-date--unavailable {
      color: $content-neutral-secondary;

      // Only fill for "today" so as not to introduce visual clutter.
      svg rect {
        fill: none;
      }
      .loanable-calendar__cell-date-content {
        text-decoration: line-through;
      }
    }
  }

  // Week and day views
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
    background-color: $background-alert-positive;
  }
  .loanable-calendar__event--unavailability {
    background-color: $background-alert-negative;
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
