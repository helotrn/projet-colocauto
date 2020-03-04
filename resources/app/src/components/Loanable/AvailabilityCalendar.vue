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
      <b-col lg="6">
        <vue-cal class="vuecal--rounded-theme" locale="fr" small
          :events="loanable.events" :selected-date="selectedMonth"
          hide-view-selector :time="false" start-week-on-sunday
          default-view="month" :disable-views="['years', 'year', 'week', 'day']">
          <template v-slot:cell-content="{ cell, view, events }">
            <span :class="`vuecal__cell-date ${availabilityClass(events)}`">
              {{ cell.content }}
            </span>
          </template>
        </vue-cal>
      </b-col>
      <b-col lg="6">
        <vue-cal class="vuecal--rounded-theme" locale="fr" small
          :events="loanable.events" :selected-date="followingMonth"
          hide-view-selector :time="false" start-week-on-sunday
          default-view="month" :disable-views="['years', 'year', 'week', 'day']">
          <template v-slot:cell-content="{ cell, view, events }">
            <span :class="`vuecal__cell-date ${availabilityClass(events)}`">
              {{ cell.content }}
            </span>
          </template>
        </vue-cal>
      </b-col>
    </b-row>

    <b-row class="availability-calendar__legend">
      <b-col>
        <ul>
          <li>Disponible</li>
          <li>Disponibilité limitée</li>
          <li>Indisponible</li>
        </ul>
      </b-col>
    </b-row>

    <b-row class="availability-calendar__description">
      <b-col>
        <div class="form-inline availability-calendar__description__default">
          <b-form-group label="Par défaut:" label-for="availability_mode" inline>
            <b-select v-model="loanable.availability_mode"
              id="availability_mode" name="availability_mode">
              <option value="never" selected>Toujours indisponible</option>
            </b-select>
          </b-form-group>
        </div>

        <loanable-exceptions :exceptions="exceptions" @input="exceptions = $event" />

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
import VueCal from 'vue-cal';
import 'vue-cal/dist/i18n/fr';

import LoanableExceptions from '@/components/Loanable/Exceptions.vue';

export default {
  name: 'LoanableAvailabilityCalendar',
  components: { LoanableExceptions, VueCal },
  props: {
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
      selectedDate: this.$dayjs().format('YYYY-MM-DD'),
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
      return this.$dayjs(this.selectedDate).format('YYYY-MM-DD');
    },
    followingMonth() {
      return this.$dayjs(this.selectedDate).add(1, 'month').format('YYYY-MM-DD');
    },
  },
  methods: {
    addException() {
      this.exceptions = [...this.exceptions, {
        available: true,
        type: null,
        scope: [],
        period: '00:00-00:00',
      }];
    },
    availabilityClass(events) {
      if (events.length === 0) {
        return 'available';
      }

      if (events.find((e) => e.period === '00:00-00:00')) {
        return 'unavailable';
      }

      return 'limited';
    },
    nextMonth() {
      this.selectedDate = this.$dayjs(this.selectedDate)
        .add(1, 'month').format('YYYY-MM-DD');
    },
    prevMonth() {
      this.selectedDate = this.$dayjs(this.selectedDate)
        .subtract(1, 'month').format('YYYY-MM-DD');
    },
    resetDate() {
      this.selectedDate = this.$dayjs().format('YYYY-MM-DD');
    },
  },
};
</script>

<style lang="scss">
.availability-calendar {
  .vdp-datepicker__calendar .cell.disabled {
    background-color: #4bd !important;
  }

  &__description {
    &__default {
      margin-bottom: 1em;

      label {
        margin-right: 1em;
      }
    }
  }

  .vuecal {
    &__title-bar {
      background-color: $white;
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
