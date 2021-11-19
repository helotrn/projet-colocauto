<template>
  <div class="exceptions">
    <b-row class="exceptions__row" v-for="(exception, index) in exceptions" :key="index">
      <b-col class="exceptions__row__remove">
        <b-button size="sm" variant="outline-danger" @click.prevent="removeException(exception)"
          >X</b-button
        >
      </b-col>
      <b-col class="exceptions__row__available">
        <b-select :value="exception.available" @change="emitChange(exception, 'available', $event)">
          <option :value="true" v-if="mode === 'never'">Rendre disponible</option>
          <option :value="false" v-if="mode === 'always'">Rendre indisponible</option>
        </b-select>
      </b-col>

      <b-col class="exceptions__row__type">
        <b-select :value="exception.type" @change="emitChange(exception, 'type', $event)">
          <option :value="null" disabled>
            Type {{ exception.available ? "de disponibilité" : "d'indisponibilité" }}
          </option>
          <option value="weekdays">Certains jours de la semaine</option>
          <option value="dates">Certaines dates en particulier</option>
          <option value="dateRange">Sélectionner une période</option>
        </b-select>

        <div v-if="exception.type === 'weekdays'">
          <b-form-checkbox-group
            :id="`weekdays-${index}`"
            class="mt-3"
            :checked="exception.scope"
            name="`weekdays-${index}`"
            @change="emitChange(exception, 'scope', $event)"
          >
            <b-form-checkbox value="SU">Dimanche</b-form-checkbox><br />
            <b-form-checkbox value="MO">Lundi</b-form-checkbox><br />
            <b-form-checkbox value="TU">Mardi</b-form-checkbox><br />
            <b-form-checkbox value="WE">Mercredi</b-form-checkbox><br />
            <b-form-checkbox value="TH">Jeudi</b-form-checkbox><br />
            <b-form-checkbox value="FR">Vendredi</b-form-checkbox><br />
            <b-form-checkbox value="SA">Samedi</b-form-checkbox>
          </b-form-checkbox-group>
        </div>
        <div v-if="exception.type === 'dates'" class="exceptions__row__type__calendar">
          <forms-date-picker
            inline
            class="mt-3"
            :disabled-dates="selectedDates(exception.scope)"
            :open-date="firstSelectedDate(selectedDates(exception.scope))"
            @input="selectDate($event, exception)"
          />
        </div>
        <div v-if="exception.type === 'dateRange'" class="exceptions__row__type__calendar">
          <forms-date-picker
            inline
            class="mt-3"
            :disabled-dates="selectedDates(exception.scope)"
            :open-date="firstSelectedDate(selectedDates(exception.scope))"
            @input="selectPeriodDate($event, exception)"
          />
        </div>
      </b-col>

      <b-col class="exceptions__row__period">
        <div v-if="exception.type" class="mb-3">
          <b-form-input
            type="text"
            :value="exception.period"
            class="mb-3"
            @blur="emitPeriodChange(exception, $event)"
            v-mask="'##:##-##:##'"
            :disabled="exception.period === '00:00-23:59' || exception.period === '00:00-24:00'"
          />

          <b-form-checkbox
            :checked="exception.period === '00:00-23:59' || exception.period === '00:00-24:00'"
            @change="togglePeriod(exception)"
          >
            Toute la journée
          </b-form-checkbox>
        </div>

        <div v-if="exception.type === 'dates'" class="exceptions__row__dates">
          <p>
            <strong>Dates sélectionnées</strong>
          </p>
          <div v-for="date in exception.scope" :key="date" class="exceptions__row__dates__date">
            {{ date }}
            <a href="#">
              <small @click.prevent="removeDate(date, exception)">Retirer</small>
            </a>
          </div>
        </div>
        <div v-if="exception.type === 'dateRange'" class="exceptions__row__dates">
          <p>
            <strong>Date de début</strong>
          </p>
          <div :key="exception.scope[0]" class="exceptions__row__dates__date">
            {{ exception.scope[0] }}
            <a href="#" v-if="exception.scope.length === 1">
              <small @click.prevent="removeDate(exception.scope[0], exception)">Retirer</small>
            </a>
          </div>
        </div>

        <div
          v-if="exception.type === 'dateRange' && exception.scope.length > 1"
          class="exceptions__row__dates"
        >
          <p class="exceptions__endTitle">
            <strong>Date de fin</strong>
          </p>
          <div :key="exception.scope[0]" class="exceptions__row__dates__date">
            {{ exception.scope[exception.scope.length - 1] }}
            <a href="#" v-if="exception.scope.length > 0">
              <small
                @click.prevent="
                  removePeriodDate(exception.scope[exception.scope.length - 1], exception)
                "
                >Retirer</small
              >
            </a>
          </div>
        </div>
      </b-col>
    </b-row>
  </div>
</template>

<script>
import FormsDatePicker from "@/components/Forms/DatePicker.vue";

export default {
  name: "LoanableExceptions",
  components: { FormsDatePicker },
  props: {
    exceptions: {
      type: Array,
      required: true,
    },
    mode: {
      type: String,
      require: true,
    },
  },
  methods: {
    emitChange(target, key, value) {
      const newExceptions = [...this.exceptions];
      const index = newExceptions.indexOf(target);
      const newTarget = { ...target };
      newTarget[key] = value;

      if (key === "type") {
        switch (value) {
          case "weekdays":
            newTarget.period = "00:00-00:00";
            newTarget.scope = [];
            break;
          case "dates":
            newTarget.period = "00:00-00:00";
            newTarget.scope = [];
            break;
          case "dateRange":
            newTarget.period = "00:00-00:00";
            newTarget.scope = [];
            break;
          default:
            // noop
            break;
        }
      }

      newExceptions.splice(index, 1, newTarget);
      this.$emit("input", newExceptions);
    },
    emitPeriodChange(target, event) {
      this.emitChange(target, "period", event.target.value);
    },
    firstSelectedDate({ dates }) {
      return dates.length > 0 ? dates[0] : new Date();
    },
    removeDate(date, exception) {
      const dates = [...exception.scope];

      const index = dates.indexOf(date);
      if (index !== -1) {
        dates.splice(index, 1);
      }

      this.emitChange(exception, "scope", dates);
    },
    removePeriodDate(date, exception) {
      const dates = [...exception.scope];
      const datesRemoved = [];
      datesRemoved.push(dates[0]);

      this.emitChange(exception, "scope", datesRemoved);
    },
    removeException(exception) {
      const newExceptions = [...this.exceptions];
      const index = newExceptions.indexOf(exception);
      newExceptions.splice(index, 1);
      this.$emit("input", newExceptions);
    },
    selectedDates(dates) {
      // FIXME Awful trick to avoid timezone issues
      return { dates: dates.map((d) => new Date(`${d} 12:00:00`)) };
    },
    selectDate(date, exception) {
      const dates = [...exception.scope];

      const index = dates.indexOf(date);
      if (index === -1) {
        dates.push(date);
        dates.sort();
      } else {
        dates.splice(index, 1);
      }

      this.emitChange(exception, "scope", dates);
    },
    selectPeriodDate(dateToAdd, exception) {
      const oldDates = [...exception.scope];
      const newDates = [];

      if (oldDates.length === 0) {
        newDates.push(dateToAdd);
        newDates.sort();

        this.emitChange(exception, "scope", newDates);
        return;
      }

      let date = oldDates[0];

      while (this.$dayjs(date).isSameOrBefore(this.$dayjs(dateToAdd), "day")) {
        const index = newDates.indexOf(date);

        if (index === -1) {
          newDates.push(date);
          newDates.sort();
        }

        date = this.$dayjs(date).add(1, "day").format("YYYY-MM-DD");
      }

      this.emitChange(exception, "scope", newDates);
    },
    togglePeriod(exception) {
      if (exception.period === "00:00-23:59" || exception.period === "00:00-24:00") {
        this.emitChange(exception, "period", "00:00-00:00");
      } else {
        this.emitChange(exception, "period", "00:00-24:00");
      }
    },
  },
};
</script>

<style lang="scss">
.exceptions {
  &__row {
    margin-bottom: 1em;

    &__remove.col {
      flex: 0 1 55px;
    }

    &__available.col {
      flex: 0 1 200px;
    }

    &__period.col {
      flex: 0 1 200px;
    }

    &__type__calendar {
      display: flex;
      justify-content: space-around;
    }

    &__dates__date {
      display: flex;
      justify-content: space-between;
      line-height: 20px;
    }
  }

  &__endTitle {
    margin-top: 16px;
  }
}
</style>
