<template>
  <div class="exceptions">
    {{ exceptions }}
    <b-row class="exceptions__row"
      v-for="(exception, index) in exceptions" :key="index">
      <b-col class="exceptions__row__available">
        <b-select :value="exception.available"
          @change="emitChange(exception, 'available', $event)">
          <option :value="true">Rendre disponible</option>
          <option :value="false">Rendre indisponible</option>
        </b-select>
      </b-col>

      <b-col class="exceptions__row__type">
        <b-select :value="exception.type"
          @change="emitChange(exception, 'type', $event)">
          <option :value="null" disabled>
            Type {{ exception.available ? 'de disponibilité' : "d'indisponibilité" }}
          </option>
          <option value="weekdays" v-if="!!exception.available">
            Certains jours de la semaine
          </option>
          <option value="dates">Certaines dates en particulier</option>
        </b-select>

        <div v-if="exception.type === 'weekdays'">
          <b-form-checkbox-group :id="`weekdays-${index}`"
            :checked="exception.scope" name="`weekdays-${index}`"
            @change="emitChange(exception, 'scope', $event)">
            <b-form-checkbox value="SU">Dimanche</b-form-checkbox>
            <b-form-checkbox value="MO">Lundi</b-form-checkbox>
            <b-form-checkbox value="TU">Mardi</b-form-checkbox>
            <b-form-checkbox value="WE">Mercredi</b-form-checkbox>
            <b-form-checkbox value="TH">Jeudi</b-form-checkbox>
            <b-form-checkbox value="FR">Vendredi</b-form-checkbox>
            <b-form-checkbox value="SA">Samedi</b-form-checkbox>
          </b-form-checkbox-group>
        </div>
        <div v-if="exception.type === 'dates'">
          <datepicker inline :disabled-dates="selectedDates(exception.scope)"
            @selected="selectDate($event, exception)" />
        </div>
      </b-col>

      <b-col class="exceptions__row__period">
        <div v-if="exception.type">
          <b-form-input type="text" :value="exception.period"
            @blur="emitPeriodChange(exception, $event)"
            v-mask="'##:##-##:##'" :disabled="exception.period === '00:00-23:59'" />

          <b-form-checkbox :checked="exception.period === '00:00-23:59'"
            @change="togglePeriod(exception)">
            Toute la journée
          </b-form-checkbox>
        </div>

        <div v-if="exception.type === 'dates'" class="exceptions__row__dates">
          <p>
            <strong>Dates sélectionnées</strong>
          </p>
          <div v-for="date in exception.scope" :key="date">
            {{ date }} <a href="#">
              <small @click.prevent="removeDate(date, exception)">Retirer</small>
            </a>
          </div>
        </div>
      </b-col>
    </b-row>
  </div>
</template>

<script>
import Datepicker from 'vuejs-datepicker';

export default {
  name: 'LoanableExceptions',
  components: { Datepicker },
  props: {
    exceptions: {
      type: Array,
      required: true,
    },
  },
  methods: {
    emitChange(target, key, value) {
      const newExceptions = [...this.exceptions];
      const index = newExceptions.indexOf(target);
      const newTarget = { ...target };
      newTarget[key] = value;

      if (key === 'type') {
        switch (value) {
          case 'weekdays':
            newTarget.period = '00:00-00:00';
            newTarget.scope = [];
            break;
          case 'dates':
            newTarget.period = '00:00-00:00';
            newTarget.scope = [];
            break;
          default:
            // noop
            break;
        }
      }

      newExceptions.splice(index, 1, newTarget);
      this.$emit('input', newExceptions);
    },
    emitPeriodChange(target, event) {
      this.emitChange(target, 'period', event.target.value);
    },
    removeDate(date, exception) {
      const dates = [...exception.scope];

      const index = dates.indexOf(date);
      if (index !== -1) {
        dates.splice(index, 1);
      }

      this.emitChange(exception, 'scope', dates);
    },
    selectedDates(dates) {
      // FIXME Awful trick to avoid timezone issues
      return { dates: dates.map(d => new Date(`${d} 12:00:00`)) };
    },
    selectDate(date, exception) {
      const dates = [...exception.scope];

      const dateStr = date.format('YYYY-MM-DD');

      const index = dates.indexOf(dateStr);
      if (index === -1) {
        dates.push(dateStr);
      } else {
        dates.splice(index, 1);
      }

      this.emitChange(exception, 'scope', dates);
    },
    togglePeriod(exception) {
      if (exception.period === '00:00-23:59') {
        this.emitChange(exception, 'period', '00:00-00:00');
      } else {
        this.emitChange(exception, 'period', '00:00-23:59');
      }
    },
  },
};
</script>

<style lang="scss">
.exceptions {
  &__row {
    margin-bottom: 1em;
  }
}
</style>
