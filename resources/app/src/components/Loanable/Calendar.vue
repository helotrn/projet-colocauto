<template>
  <div :style="{position: 'relative' }">
  <b-button
    class="top-corner"
    variant="primary"
    @click="createLoanForToday"
  >
    Créer
  </b-button>
  <vue-cal
    ref="vuecal"
    :class="classList"
    :disable-views="['years', 'year']"
    :active-view="defaultView"
    :time-step="60"
    :time-cell-height="variant === 'small' ? 48 : 72"
    :events="vueCalEvents"
    locale="fr"
    :small="variant === 'small'"
    @ready="$emit('ready', $event)"
    @view-change="$emit('view-change', $event)"
    :on-event-click="showDetails"

    :editable-events="{ create: !isTouchScreen, resize: !isTouchScreen }"
    :snap-to-time="15"
    :drag-to-create-threshold="30"
    :on-event-create="registerCancel"
    @event-duration-change="changeDuration"
    @event-drag-create="createNewLoan"
    @cell-click="registerClick"
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

    <template #cell-content="{ cell, view, events, goNarrower }">
      <calendar-month-cell-content
        v-if="view.id === 'month'"
        :availability="getMonthDayAvailability(events, cell)"
        :current="cell.today"
        @click="goNarrower"
      >
        {{ cell.content }}
      </calendar-month-cell-content>
    </template>

    <template #event="{ event, view }">
      <div class="vuecal__event-wrapper" :class="
        event.endTimeMinutes - event.startTimeMinutes > 90 ? 'vuecal__event_large' :
        event.endTimeMinutes - event.startTimeMinutes > 60 ? 'vuecal__event_medium' :
        event.endTimeMinutes - event.startTimeMinutes > 30 ? 'vuecal__event_small' :
        'vuecal__event_tiny'
      ">
        <template  v-if="event.data && event.data.user">
          <span class="vuecal__event_first_name">{{ event.data.user.first_name }}</span>
          <span class="vuecal__event_last_name">{{ event.data.user.last_name }}</span>
          <span class="vuecal__event_reason">{{ event.data.reason }}</span>
        </template>
        <div v-else class="vuecal__event-title" v-html="event.title"></div>

        <div class="vuecal__event_time" v-if="event.type=='loan'">
          {{ event.start.formatTime("HH:mm") }}
          <span>&nbsp;- {{ event.end.formatTime("HH:mm") }}</span>
        </div>
      </div>
    </template>
  </vue-cal>
  <layout-loading class="section-loading-indicator" v-if="loading" />

  <b-modal v-model="showDialog"
    :title="loanable.name"
    id="loanable-calendar-modal"
    hide-footer
    header-class="p-2 border-bottom-0"
    @hidden="closeDialog"
  >
    <b-card no-body v-if="selectedEvent.data">
      <layout-loading class="section-loading-indicator" v-if="loanLoading" />
      <b-container v-else>
        <div class="d-flex flex-column align-items-center">
          <user-avatar :user="selectedEvent.data.borrower.user" variant="cut-out" />
          <div>
            <span>{{ selectedEvent.data.borrower.user.full_name }}</span>
            <span class="d-block text-center">{{ selectedEvent.data.reason }}</span>
          </div>
        </div>
        <div class="d-flex flex-column align-items-center my-2">
          <div v-if="multipleDays">
            {{ selectedEvent.data.departure_at | date }} {{ selectedEvent.data.departure_at | time }}<br />
            {{ selectedEvent.data.actual_return_at | date }} {{ selectedEvent.data.actual_return_at | time }}
          </div>
          <div v-else>
            {{ selectedEvent.data.departure_at | date }}<br />
            {{ selectedEvent.data.departure_at | time }} à {{ selectedEvent.data.actual_return_at | time }}
          </div>
        </div>
        <div class="d-flex justify-content-around w-50 mx-auto mt-4">
          <b-button
              variant="outline-primary"
              :to="selectedEvent.uri"
            >
              Consulter
            </b-button>
            <b-button
              variant="outline-primary"
              @click="changeSelected"
            >
              Modifier
            </b-button>
        </div>
        <div class="d-flex justify-content-around w-50 mx-auto mt-4">
          <loan-action-buttons
            class="mb-3"
            :actions="['cancel']"
            :item="selectedEvent.data"
            @extension="addExtension"
            @cancel="cancelLoan"
            @incident="addIncident('accident')"
          />
        </div>
      </b-container>
    </b-card>
    <b-card no-body v-else-if="newEvent.data">
      <layout-loading class="section-loading-indicator" v-if="loading" />
      <b-container v-else>
        <div class="d-flex flex-column align-items-center">
          <user-avatar :user="newEvent.data.borrower.user" variant="cut-out" />
          <div>{{ newEvent.data.borrower.user.full_name }}</div>
        </div>
        <div class="d-flex flex-column align-items-center my-2">
            <forms-validated-input
              name="departure_at"
              label="Départ"
              type="datetime"
              v-model="newEvent.start"
            />
            <forms-validated-input
              name="return_at"
              label="Retour"
              type="datetime"
              v-model="newEvent.end"
            />
        </div>
        <b-alert v-if="!newEvent.data.available" variant="danger" show>
          Le véhicule n'est pas disponible sur ces horaires
        </b-alert>
        <div class="d-flex flex-column align-items-center mt-4">
          <b-button
            size="sm"
            variant="outline-primary"
            @click="askLoan"
          >
            Réserver
          </b-button>
        </div>
      </b-container>
    </b-card>
    <b-card no-body v-else-if="extendLoan.data">
      <layout-loading class="section-loading-indicator" v-if="loading" />
      <b-container v-else>
        <div class="d-flex flex-column align-items-center">
          <user-avatar :user="extendLoan.data.borrower.user" variant="cut-out" />
          <div>
            <span>{{ extendLoan.data.borrower.user.full_name }}</span>
            <span class="d-block text-center">{{ extendLoan.data.reason }}</span>
          </div>
        </div>
        <template v-if="extendLoan.updated">
          <div class="d-flex flex-column align-items-center my-2">
            {{ extendLoan.data.departure_at | date }}<br />
            {{ extendLoan.data.departure_at | time }} à {{ extendLoan.data.actual_return_at | time }}
          </div>
          <div class="d-flex flex-column align-items-center my-2">
            <b-alert variant="success" show>
              Modification des horaires prise en compte
            </b-alert>
          </div>
          <div class="d-flex justify-content-around mx-auto mt-4">
            <b-button
              variant="outline-primary"
              @click="showDialog=false"
            >
              Fermer
            </b-button>
          </div>
        </template>
        <template v-else>
          <div class="d-flex flex-column align-items-center my-2">
            Horaires de l'emprunt
            <div>
              {{ extendLoan.data.departure_at | date }}<br />
              {{ extendLoan.data.departure_at | time }} à {{ extendLoan.data.actual_return_at | time }}
            </div>
            <div>
              <h2>Modifier les horaires</h2>
                  <forms-validated-input
                    name="departure_at"
                    label="Départ"
                    type="datetime"
                    v-model="extendLoan.newDates.start"
                  />
                  <forms-validated-input
                    name="return_at"
                    label="Retour"
                    type="datetime"
                    v-model="extendLoan.newDates.end"
                  />
            </div>
          </div>
          <div v-if="extendLoan.error" class="d-flex flex-column align-items-center my-2">
            <b-alert variant="danger" show>
              {{extendLoan.error}}
            </b-alert>
          </div>
          <div class="d-flex justify-content-around mx-auto mt-4">
            <b-button
              variant="danger"
              @click="cancelChange"
            >
              Annuler
            </b-button>
            <b-button
              variant="primary"
              @click="confirmChange"
            >
              Valider la modification
            </b-button>
          </div>
        </template>
      </b-container>
    </b-card>
  </b-modal>
  </div>
</template>

<script>
import VueCal from "vue-cal";
import Vue from "vue";

import CalendarMonthCellContent from "@/components/Loanable/CalendarMonthCellContent.vue";
import UserAvatar from "@/components/User/Avatar.vue";
import FormsValidatedInput from "@/components/Forms/ValidatedInput.vue";
import LoanActionButtons from "@/components/Loan/ActionButtons.vue";

import UserMixin from "@/mixins/UserMixin";
import CalendarMixin from "@/mixins/CalendarMixin";
import LoanStepsSequence from "@/mixins/LoanStepsSequence";

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
    loanable: {
      type: Object,
      required: false,
    },
    loading: {
      type: Boolean,
      default: true,
    },
  },
  data: () => ({
    selectedEvent: {},
    newEvent: {},
    showDialog: false,
    loanLoading: false,
    cancelNewEvent: undefined,
    extendLoan: {},
  }),
  mixins: [UserMixin, CalendarMixin, LoanStepsSequence],
  components: {
    VueCal,
    "calendar-month-cell-content": CalendarMonthCellContent,
    UserAvatar,
    FormsValidatedInput,
    LoanActionButtons,
  },
  mounted(){
    // scroll the calendar to show 8-20 hours
    if(this.variant === "small") {
      const timeCellHeight = 48;
      const hours = 8;
      this.$el.querySelector('.vuecal__bg').scrollTo({ top: hours * timeCellHeight });
    }
  },
  computed: {
    classList: function () {
      let classList = {
        "loanable-calendar": true,
      };
      if (this.variant) {
        classList["loanable-calendar--" + this.variant] = true;
      }
      // if current user is the loanable owner
      classList["loanable-owner"] = this.user.id == this.loanable.owner?.user?.id
      return classList;
    },
    vueCalEvents: function () {
      let baseEvent = {
        deletable: false,
        resizable: true,
        draggable: false,
      };

      let vueCalEvents = this.events.map((e) => {
        e = { ...baseEvent, ...e };
        if(!Array.isArray(e.class)) e.class = [e.class];

        if (e.type === "availability") {
          // Availability events go in the background.
          e.background = true;
          e.resizable = false;
          if (e.data.available) {
            e.class.push("loanable-calendar__event--availability");
          } else {
            e.class.push("loanable-calendar__event--unavailability");
          }
        } else if (e.type === "loan") {
          e.class.push("loanable-calendar__event");
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
    multipleDays() {
      if( !this.selectedEvent || !this.selectedEvent.data ) return false;
      return (
        this.$dayjs(this.selectedEvent.data.departure_at).format("YYYY-MM-DD") !==
        this.$dayjs(this.selectedEvent.data.actual_return_at).format("YYYY-MM-DD")
      );
    },
    isTouchScreen() {
      return matchMedia('(hover: none), (pointer: coarse)').matches;
    },
  },
  methods: {
    getMonthDayAvailability(events, cell) {
      const cellStartTime = this.$dayjs(cell.startDate).startOfDay();
      const cellEndTime = cellStartTime.add(1, "day").subtract(1, "second");

      // all past days are unavailable
      if(this.$dayjs().diff(cellEndTime) > 0 ) return "unavailable";

      let eventStartTime, eventEndTime;

      let availability = "available";

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
          availability = events[e].data.available ? "available" : "unavailable";
        } else {
          // If event does not span the whole day, then partially available.
          availability = "partially-available";
        }
      }

      return availability;
    },
    showDetails (event, e) {
      if(event.background) return;

      this.getLoanDetails(event).then(data => {
        this.selectedEvent.data = { ...data, ...this.selectedEvent.data };
        this.loanLoading = false;
        this.item = this.selectedEvent.data;
      });

      this.loanLoading = true;
      this.selectedEvent = event
      this.showDialog = true

      // Prevent navigating to narrower view (default vue-cal behavior).
      e.stopPropagation()
    },
    async createNewLoan (event) {
      // do not create loans shorter than 30 minutes
      if( event.endTimeMinutes - event.startTimeMinutes < 30 ) {
        this.cancelNewEvent();
        this.cancelNewEvent = undefined;
        return false;
      }

      // let the popup display the warning message in case of overlaping

      this.loanLoading = true;
      this.showDialog = true;
      try {
        await this.$store.dispatch("loans/loadEmpty");
      } finally {
        this.loanLoading = false;
      }
      await this.testLoan(event.start, event.end, this.loanable.id);

      this.newEvent = {
        ...event,
        data: {
          status: 'creating',
          borrower: {user: this.user},
          available: this.$store.state.loans.item.loanable.available,
        },
        start: event.start.format("YYYY-MM-DD HH:mm:00"),
        end: event.end.format("YYYY-MM-DD HH:mm:00"),
      };
    },
    registerCancel (event, deleteEvent) {
      // register cancel method to use later when needed
      this.cancelNewEvent = deleteEvent;
      return true;
    },
    registerClick(date){
      // check if a drag-n-drop event creation is in process
      if(this.cancelNewEvent) return;

      // create a 2 hour event from the clicked point
      this.createNewLoan(this.$refs.vuecal.createEvent(date, 120));
    },
    createLoanForToday(){
      // now rounded to the next quarter of hour
      let date = this.$dayjs().minute(Math.ceil(this.$dayjs().minute() / 15) * 15).toDate();
      // create a 2 hour event from now
      this.registerClick(date);
    },
    async askLoan() {

      this.$store.commit("loans/patchItem", {
        departure_at: this.$dayjs(this.newEvent.start).format("YYYY-MM-DD HH:mm:ss"),
        duration_in_minutes: this.$dayjs(this.newEvent.end).diff(this.newEvent.start, 'minutes'),
        borrower: {
          ...this.user.borrower,
          user: {
            id: this.user.id,
            full_name: this.user.full_name,
          },
        },
        borrower_id: this.user.borrower.id,
        loanable: {...this.loanable, available: true},
        car: this.loanable,
        loanable_id: this.loanable.id,
        platform_tip: 0,
      });
      this.cancelNewEvent = undefined;
      this.showDialog = false;

      this.$router.push("/loans/new");
    },
    async changeDuration({event, oldDate, originalEvent}) {

      // do not make loans shorter than 30 minutes
      if( event.endTimeMinutes - event.startTimeMinutes < 30 ) {
        this.restoreEventDisplay(originalEvent.uri, this.events);
        return false;
      }

      if( event.end > originalEvent.end && event.end - originalEvent.end > 15*60*1000 ){
        // check if the resize is overlaping with anoter loan
        let overlaping = this.events.find(e =>
          this.$dayjs(e.start) < event.end
          && this.$dayjs(e.end) > originalEvent.end
        );
        if( overlaping ) {
          // restore the event and do not go further
          this.restoreEventDisplay(originalEvent.uri, this.events);
          return false;
        }

        await this.testLoan(originalEvent.end, event.end, this.loanable.id);
      }

      this.extendLoan = {
        ...event,
        newDates: {
          start: event.start.format("YYYY-MM-DD HH:mm:00"),
          end: event.end.format("YYYY-MM-DD HH:mm:00"),
        },
        available: event.end - originalEvent.end > 15*60*1000 ? this.$store.state.loans.item.loanable.available : true,
      };
      // get more information about the loan
      this.loanLoading = true;
      this.getLoanDetails(event).then(data => {
        this.extendLoan.data = { ...data, ...this.extendLoan.data };
        this.loanLoading = false;
      });
      this.showDialog = true
    },
    changeSelected(){
      this.extendLoan = {
        ...this.selectedEvent,
        newDates: {
          start: this.selectedEvent.start.format("YYYY-MM-DD HH:mm:00"),
          end: this.selectedEvent.end.format("YYYY-MM-DD HH:mm:00"),
        },
        available: true,
      };
      this.selectedEvent = {};
      this.showDialog = true;
    },
    confirmChange(){
      this.extendLoan.updated = false;
      this.updateLoanDates(this.extendLoan, this.extendLoan.newDates).then(() => {
        this.extendLoan.data = this.$store.state.loans.item;
        this.extendLoan.updated = true;
        this.updateEventDisplay(this.extendLoan, this.events);
      }).catch(error => {
        Vue.set(this.extendLoan, 'error', Object.values(this.$store.state.loans.error.response.data.errors).map(v => v.join()).join());
      });
    },
    cancelChange(){
      // restore the event and do not go further
      this.restoreEventDisplay(this.extendLoan.uri, this.events);
      this.showDialog = false;
    },
    closeDialog(){
      // when a new loan is in creation, cancel it
      if (this.cancelNewEvent) {
        this.cancelNewEvent();
        this.cancelNewEvent = undefined;
      }
      // when a loan extension is in progress, revert it
      if (this.extendLoan.data && !this.extendLoan.updated) {
        this.restoreEventDisplay(this.extendLoan.uri, this.events);
      }
      // cleanup
      if( this.extendLoan.data) {
        this.extendLoan = {};
      }
      if ( this.selectedEvent.data ) {
        this.selectedEvent = {};
      }
      if ( this.newEvent.data ) {
        this.newEvent = {};
      }
    },
  },
};
</script>

<style lang="scss">
.btn.top-corner {
  position: absolute;
  right: 0;
}
.loanable-calendar {
  font-variant-numeric: tabular-nums;

  &.vuecal {
    box-shadow: none;
  }
  .vuecal__header {
    background: white;
  }
  .vuecal__menu {
    padding: 16px 0;
    background: white;
  }

  &.loanable-calendar--small {
    font-size: 0.82rem;
    max-height: 35.68rem;
    .vuecal__title-bar,
    .vuecal__weekdays-headings {
      background-color: transparent;
      // Needs to be repeated for title-bar to overload .vuecal--xsmall .vuecal__title-bar
      font-size: 1rem;
      padding-top: 10px;
      padding-bottom: 10px;
    }
    .vuecal__heading {
      height: inherit;
      min-height: 2em;
      line-height: 1.3;
    }
    .weekday-label .small {
      font-size: 1em;
    }
  }

  .vuecal__no-event {
    display: none;
  }

  .week-view.vuecal__cells,
  .day-view.vuecal__cells {
    background-color: $background-alert-positive;
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

  /* Month view. */
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
    height: 3.5rem;
  }

  .vuecal__cell--out-of-scope {
    .loanable-calendar-month-cell-content {
      &__content {
        opacity: 0.4;
        color: black;
        text-decoration: line-through;
      }
      &__background {
        display: none;
      }
    }
  }

  .loanable-calendar-month-cell-content {
    font-size: 1rem;
    background: white;
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

  /* Week and day views
  // Styling the time axis.
  // Specificity seems necessary here. */
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
  .vuecal__event-wrapper {
      margin: 3px;
      background-color: white;
      border-radius: 5px;
      height: calc(100% - 6px);
      display: flex;
      flex-direction: column;
      justify-content: center;

      .vuecal__event-title {
        margin: 0 0.4em 1em;
      }
    }
  .loanable-calendar__event--unavailability .vuecal__event-wrapper {
    background-color: transparent;
  }
  &.loanable-owner {
    /* only if user=owner */
    .loanable-calendar__event--unavailability {
      z-index: -1;
      .vuecal__event-wrapper {
        display: none;
      }
    }
    .vuecal__cell--has-events {
      z-index: 2;
    }
  }
  .vuecal__event {
    background-color: transparent;
    &.loanable-calendar__event--unavailability {
      background-color: #FFE6E4;
    }
    &.color-persian-green .vuecal__event-wrapper {
      color: white;
      background-color: #00ada8;
    }
    &.color-sunglo .vuecal__event-wrapper {
      color: white;
      background-color: #B56457;
    }
    &.color-teal .vuecal__event-wrapper {
      color: white;
      background-color: #127A8B;
    }
    &.color-outrageous-orange .vuecal__event-wrapper {
      color: white;
      background-color: #FF6133;
    }
    &.color-governor-bay .vuecal__event-wrapper {
      color: white;
      background-color: #556093;
    }
    &.color-sun .vuecal__event-wrapper {
      color: white;
      background-color: #F38433;
    }
    &.color-kournikova .vuecal__event-wrapper {
      color: white;
      background-color: #FFCA56;
    }
    &.color-buccaneer .vuecal__event-wrapper {
      color: white;
      background-color: #664B4B;
    }

    &_tiny {
      .vuecal__event_reason,
      .vuecal__event_time,
      .vuecal__event_last_name {
        display: none;
      }
    }
    &_small {
      .vuecal__event_reason,
      .vuecal__event_time {
        display: none;
      }
    }
    &_medium {
      .vuecal__event_reason {
        display: none;
      }
    }
  }
  .loanable-calendar__event--loan_in_process + .loanable-calendar__event--unavailability {
     background-color: transparent;
  }
}
#loanable-calendar-modal .modal-dialog .card.shadow {
  box-shadow: none !important;
}
</style>
<style scoped>
.layout-loading {
  position: absolute;
  width: 100%;
  height: 100%;
  top: 0;
  align-items: center;
}
.layout-loading >>> img {
  max-height: 200px;
}
</style>
