<template>
  <div class="availability-rules">
    <b-row class="availability-rules__header">
      <b-col>
        <h2>Disponibilités</h2>
      </b-col>
      <b-col class="text-right">
        <b-button @click="resetDate()">Auj.</b-button>

        <b-button-group class="ml-1">
          <b-button @click="prevMonth()">&lt;</b-button>
          <b-button @click="nextMonth()">&gt;</b-button>
        </b-button-group>
      </b-col>
    </b-row>

    <b-row class="availability-rules__calendars">
      <div v-if="changed" class="availability-rules__calendars--changed">
        <div>Sauvegardez pour mettre à jour l'aperçu des disponibilités.</div>
      </div>

      <b-col lg="6">
        <vue-cal
          class="vuecal--rounded-theme"
          locale="fr"
          small
          :events="loanable.events"
          :selected-date="selectedMonth"
          hide-view-selector
          :time="false"
          start-week-on-sunday
          default-view="month"
          :disable-views="['years', 'year', 'week', 'day']"
        >
          <template v-slot:cell-content="{ cell, view, events }">
            <span
              :class="`vuecal__cell-date ${availabilityClass(
                loanable.availability_mode,
                events,
                cell
              )}`"
            >
              {{ cell.content }}
            </span>
          </template>
        </vue-cal>
      </b-col>
      <b-col lg="6">
        <vue-cal
          class="vuecal--rounded-theme"
          locale="fr"
          small
          :events="loanable.events"
          :selected-date="followingMonth"
          hide-view-selector
          :time="false"
          start-week-on-sunday
          default-view="month"
          :disable-views="['years', 'year', 'week', 'day']"
        >
          <template v-slot:cell-content="{ cell, view, events }">
            <span
              :class="`vuecal__cell-date ${availabilityClass(
                loanable.availability_mode,
                events,
                cell
              )}`"
            >
              {{ cell.content }}
            </span>
          </template>
        </vue-cal>
      </b-col>
    </b-row>

    <b-row class="availability-rules__legend">
      <b-col>
        <ul>
          <li>
            <span class="availability-rules__legend__available" />
            Disponible
          </li>
          <li>
            <span class="availability-rules__legend__limited" />
            Disponibilité limitée
          </li>
          <li>
            <span class="availability-rules__legend__unavailable" />
            Indisponible
          </li>
        </ul>
      </b-col>
    </b-row>

    <b-row>
      <b-col>
        <loanable-calendar
          defaultView="month"
          :events="events"
          @ready="getEvents"
          @view-change="getEvents"
        ></loanable-calendar>
      </b-col>
    </b-row>

    <b-row class="availability-rules__description">
      <b-col>
        <div class="form-inline availability-rules__description__default">
          <b-form-group label="Par défaut:" label-for="availability_mode" inline>
            <b-select
              v-model="loanable.availability_mode"
              @change="exceptions = []"
              id="availability_mode"
              name="availability_mode"
            >
              <option value="never" selected>Toujours indisponible</option>
              <option value="always" selected>Toujours disponible</option>
            </b-select>
          </b-form-group>
        </div>

        <b-row>
          <b-col>
            <p>Sauf&nbsp;:</p>
            <loanable-exceptions
              :mode="loanable.availability_mode"
              :exceptions="exceptions"
              @input="exceptions = $event"
            />
          </b-col>
        </b-row>

        <b-row>
          <b-col>
            <a href="#" @click.prevent="addException">+ Ajouter une exception</a>
          </b-col>
        </b-row>
      </b-col>
    </b-row>
  </div>
</template>

<script>
import Vue from "vue";

import VueCal from "vue-cal";
import "vue-cal/dist/i18n/fr";

import LoanableCalendar from "@/components/Loanable/Calendar.vue";
import LoanableExceptions from "@/components/Loanable/Exceptions.vue";

export default {
  name: "LoanableAvailabilityRules",
  components: {
    LoanableCalendar,
    LoanableExceptions,
    VueCal,
  },
  props: {
    changed: {
      type: Boolean,
      required: false,
      default: false,
    },
    loanable: {
      type: Object,
      required: true,
    },
    loading: {
      type: Boolean,
      required: false,
      default: false,
    },
  },
  data() {
    return {
      selectedDate: this.$dayjs().format("YYYY-MM-DD"),
      events: [],
    };
  },
  computed: {
    exceptions: {
      get() {
        return JSON.parse(this.loanable.availability_json);
      },
      set(val) {
        this.loanable.availability_json = JSON.stringify(val);
      },
    },
    selectedMonth() {
      return this.$dayjs(this.selectedDate).format("YYYY-MM-DD");
    },
    followingMonth() {
      return this.$dayjs(this.selectedDate).add(1, "month").format("YYYY-MM-DD");
    },
  },
  methods: {
    addException() {
      this.exceptions = [
        ...this.exceptions,
        {
          available: this.loanable.availability_mode === "never",
          type: null,
          scope: [],
          period: "00:00-24:00",
        },
      ];
    },
    availabilityClass(availabilityMode, events, cell) {
      const today = this.$dayjs().startOfDay();
      const inAYear = this.$dayjs().add(365, "day");

      const cellStartTime = this.$dayjs(cell.startDate).startOfDay();
      const cellEndTime = cellStartTime.add(1, "day");

      // All what's in the past is unavailable.
      if (cellStartTime.isBefore(today)) {
        return "unavailable";
      }

      // All what's in in more than a year is unknown.
      if (cellStartTime.isSameOrAfter(inAYear)) {
        return "unknown";
      }

      let eventStartTime, eventEndTime;
      if (availabilityMode == "always") {
        let nEvents = events.length;

        for (let e = 0; e < nEvents; e++) {
          eventStartTime = this.$dayjs(events[e].start);
          eventEndTime = this.$dayjs(events[e].end);

          // If event spans the whole day, then unavailable.
          if (
            0 == eventStartTime.diff(cellStartTime, "seconds") &&
            0 == eventEndTime.diff(cellEndTime, "seconds")
          ) {
            return "unavailable";
          }
        }

        // If an event was found, that does not span the whole day, then
        // limited availability.
        if (nEvents > 0) return "limited";
      } else {
        let nEvents = events.length;

        for (let e = 0; e < nEvents; e++) {
          eventStartTime = this.$dayjs(events[e].start);
          eventEndTime = this.$dayjs(events[e].end);

          // If event spans the whole day, then available.
          if (
            0 == eventStartTime.diff(cellStartTime, "seconds") &&
            0 == eventEndTime.diff(cellEndTime, "seconds")
          ) {
            return "available";
          }
        }

        // If an event was found, that does not span the whole day, then
        // limited availability.
        if (nEvents > 0) return "limited";
      }

      // No event found, then default availability applies.
      return availabilityMode == "always" ? "available" : "unavailable";
    },
    nextMonth() {
      this.selectedDate = this.$dayjs(this.selectedDate).add(1, "month").format("YYYY-MM-DD");
    },
    prevMonth() {
      this.selectedDate = this.$dayjs(this.selectedDate).subtract(1, "month").format("YYYY-MM-DD");
    },
    resetDate() {
      this.selectedDate = this.$dayjs().format("YYYY-MM-DD");
    },
    getEvents({ view, startDate, endDate, firstCellDate, lastCellDate, week }) {
      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();

      let start, end;

      // Include out-of-scope days in month view.
      if (view === "month") {
        // Must convert [, ] interval to [, ) by adding one second to the end time.
        start = this.$dayjs(firstCellDate);
        end = this.$dayjs(lastCellDate).add(1, "s");
      } else {
        // Must convert [, ] interval to [, ) by adding one second to the end time.
        start = this.$dayjs(startDate);
        end = this.$dayjs(endDate).add(1, "s");
      }

      try {
        Vue.axios
          .get(`/loanables/${this.loanable.id}/availability`, {
            params: {
              start: start.format("YYYY-MM-DD HH:mm:ss"),
              end: end.format("YYYY-MM-DD HH:mm:ss"),
              responseMode: "available",
            },
            cancelToken: cancelToken.token,
          })
          .then((response) => {
            this.events = response.data.map((e) => {
              e.type = "availability";
              return e;
            });
          });
      } catch (e) {
        throw e;
      }
    },
  },
};
</script>

<style lang="scss">
.availability-rules {
  .vdp-datepicker__calendar .cell.selected {
    background-color: transparent !important;
  }

  .vdp-datepicker__calendar .cell.disabled {
    background-color: #4bd !important;
  }

  &__calendars {
    position: relative;

    &--changed {
      z-index: 100;
      opacity: 0.8;
      background-color: $white;
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: center;
      text-align: center;
    }
  }

  &__description {
    &__default {
      margin-bottom: 1em;

      label {
        margin-right: 1em;
      }
    }
  }

  &__legend {
    margin-top: 1em;

    ul {
      padding: 0;
      list-style-type: none;
    }

    li {
      display: inline-block;
      margin-right: 30px;
    }

    span {
      display: inline-block;
      width: 10px;
      height: 10px;
      border-radius: 5px;
    }

    &__available {
      background-color: $success;
    }

    &__limited {
      background-color: $warning;
    }

    &__unavailable {
      background-color: $danger;
    }

    &__unknown {
      background-color: $info;
    }
  }

  .vuecal {
    background-color: $white;

    &__title-bar {
      background-color: transparent;
    }

    &__title {
      font-size: 16px;
      color: $locomotion-dark-blue;
    }
    &__cell-date {
      &.available {
        color: $success;
      }

      &.unavailable {
        color: $danger;
      }

      &.limited {
        color: $warning;
      }
    }
  }
}
</style>
