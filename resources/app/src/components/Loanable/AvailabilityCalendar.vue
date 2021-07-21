<template>
  <div class="availability-calendar">
    <b-row class="availability-calendar__header">
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

    <b-row class="availability-calendar__calendars">
      <div v-if="changed" class="availability-calendar__calendars--changed">
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
            <span :class="`vuecal__cell-date ${availabilityClass(events, cell)}`">
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
            <span :class="`vuecal__cell-date ${availabilityClass(events, cell)}`">
              {{ cell.content }}
            </span>
          </template>
        </vue-cal>
      </b-col>
    </b-row>

    <b-row class="availability-calendar__legend">
      <b-col>
        <ul>
          <li>
            <span class="availability-calendar__legend__available" />
            Disponible
          </li>
          <li>
            <span class="availability-calendar__legend__limited" />
            Disponibilité limitée
          </li>
          <li>
            <span class="availability-calendar__legend__unavailable" />
            Indisponible
          </li>
        </ul>
      </b-col>
    </b-row>

    <b-row class="availability-calendar__description">
      <b-col>
        <div class="form-inline availability-calendar__description__default">
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
            <loanable-exceptions
              :mode="loanable.availability_mode"
              :exceptions="exceptions"
              @input="exceptions = $event"
            />
          </b-col>
        </b-row>

        <b-row>
          <b-col>
            <a href="#" @click.prevent="addException">+ Ajouter une règle de disponibilité</a>
          </b-col>
        </b-row>
      </b-col>
    </b-row>
  </div>
</template>

<script>
import VueCal from "vue-cal";
import "vue-cal/dist/i18n/fr";

import LoanableExceptions from "@/components/Loanable/Exceptions.vue";

export default {
  name: "LoanableAvailabilityCalendar",
  components: { LoanableExceptions, VueCal },
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
          period: "00:00-23:59",
        },
      ];
    },
    availabilityClass(events, cell) {
      const now = this.$dayjs().format("YYYY-MM-DD");
      const inAYear = this.$dayjs().add(365, "day").format("YYYY-MM-DD");
      if (cell.startDate.format("YYYY-MM-DD") < now) {
        return "unavailable";
      }

      if (cell.startDate.format("YYYY-MM-DD") >= inAYear) {
        return "unknown";
      }

      if (events.length === 0) {
        return "available";
      }

      if (events.find((e) => e.period === "00:00-00:00")) {
        return "unavailable";
      }

      return "limited";
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
  },
};
</script>

<style lang="scss">
.availability-calendar {
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

    &__arrow {
      display: none;
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
