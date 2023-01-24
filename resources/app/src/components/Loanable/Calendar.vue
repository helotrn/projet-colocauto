<template>
  <div>
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
    :time-from="8 * 60"
    :time-to="20 * 60"
    :on-event-click="showDetails"

    :editable-events="{ create: true }"
    :snap-to-time="15"
    :on-event-create="registerCancel"
    @event-drag-create="createNewLoan"
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
  </vue-cal>

  <b-modal v-model="showDialog"
    :title="loanable.name"
    id="loanable-calendar-modal"
    hide-footer
    header-class="p-2 border-bottom-0"
    @close="cancelNewEvent ? cancelNewEvent() : ''"
  >
    <b-card no-body v-if="selectedEvent.data">
      <layout-loading class="section-loading-indicator" v-if="loading" />
      <b-row v-else>
        <b-col lg="6">
          <b-row>
            <b-col class="loan-info-box__image__wrapper">
            <user-avatar :user="selectedEvent.data.borrower.user" variant="cut-out" />

            <div class="loan-info-box__name">
              <span>
                <span class="loan-info-box__name__loanable">{{ selectedEvent.data.borrower.user.full_name }}</span>
                <span class="loan-info-box__reason__loanable d-block text-center">{{ selectedEvent.data.reason }}</span>
              </span>
            </div>
            </b-col>
          </b-row>
        </b-col>

        <b-col class="loan-info-box__details mb-2 mt-2" lg>
          <span>
            <span v-if="multipleDays">
                {{ selectedEvent.data.departure_at | date }} {{ selectedEvent.data.departure_at | time }}<br />
                {{ selectedEvent.data.actual_return_at | date }} {{ selectedEvent.data.actual_return_at | time }}
              </span>
              <span v-else>
              {{ selectedEvent.data.departure_at | date }}<br />
              {{ selectedEvent.data.departure_at | time }} à {{ selectedEvent.data.actual_return_at | time }}
            </span>
          </span>
        </b-col>

        <b-col class="loan-info-box__actions" lg>
          <div>
            <b-button
              size="sm"
              variant="outline-primary"
              :to="selectedEvent.uri"
            >
              Consulter
            </b-button>
          </div>
        </b-col>
      </b-row>
    </b-card>
    <b-card no-body v-else-if="newEvent.data">
      <layout-loading class="section-loading-indicator" v-if="loading" />
      <b-row v-else>
        <b-col lg="6">
          <b-row>
            <b-col class="loan-info-box__image__wrapper">
              <user-avatar :user="newEvent.data.borrower.user" variant="cut-out" />

              <div class="loan-info-box__name">
                <span>
                  <span class="loan-info-box__name__loanable">{{ newEvent.data.borrower.user.full_name }}</span>
                </span>
              </div>
            </b-col>
            <b-col class="loan-info-box__details mb-2 mt-2" lg>
              <span>
                <span>
                  {{ newEvent.start | date }}<br />
                  {{ newEvent.start | time }} à {{ newEvent.end | time }}
                </span>
              </span>
            </b-col>

            <b-col class="loan-info-box__actions" lg>
              <div>
                <b-button
                  size="sm"
                  variant="outline-primary"
                  @click="askLoan"
                >
                  Demande d'emprunt
                </b-button>
              </div>
            </b-col>
          </b-row>
        </b-col>
      </b-row>
    </b-card>
  </b-modal>
  </div>
</template>

<script>
import VueCal from "vue-cal";
import Vue from "vue";

import CalendarMonthCellContent from "@/components/Loanable/CalendarMonthCellContent.vue";
import UserAvatar from "@/components/User/Avatar.vue";
import UserMixin from "@/mixins/UserMixin";

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
  },
  data: () => ({
    selectedEvent: {},
    newEvent: {},
    showDialog: false,
    loading: false,
    cancelNewEvent: undefined,
  }),
  mixins: [UserMixin],
  components: {
    VueCal,
    "calendar-month-cell-content": CalendarMonthCellContent,
    UserAvatar,
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
      };

      let vueCalEvents = this.events.map((e) => {
        e = { ...baseEvent, ...e };
        e.class = ["loanable-calendar__event"];

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
    multipleDays() {
      if( !this.selectedEvent || !this.selectedEvent.data ) return false;
      return (
        this.$dayjs(this.selectedEvent.data.departure_at).format("YYYY-MM-DD") !==
        this.$dayjs(this.selectedEvent.data.actual_return_at).format("YYYY-MM-DD")
      );
    },
  },
  methods: {
    getMonthDayAvailability(events, cell) {
      const cellStartTime = this.$dayjs(cell.startDate).startOfDay();
      const cellEndTime = cellStartTime.add(1, "day");

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

      const { CancelToken } = Vue.axios;
      const cancelToken = CancelToken.source();

      Vue.axios
          .get(event.uri, {
            params: {
              fields: 'departure_at, actual_return_at, status, borrower.user.full_name,'
              +' borrower.user.avatar, borrower.user.email, borrower.user.phone',
            },
            cancelToken: cancelToken.token,
          })
          .then(response => {
            if(response.status == 200) {
              this.selectedEvent.data = { ...response.data, ...this.selectedEvent.data };
            }
            this.loading = false;
          });
      this.loading = true;
      this.selectedEvent = event
      this.showDialog = true

      // Prevent navigating to narrower view (default vue-cal behavior).
      e.stopPropagation()
    },
    async createNewLoan (event) {
      // check if the event is not overlaping with another
      let overlaping = this.vueCalEvents.filter(e =>
        this.$dayjs(e.start) < event.end
        && this.$dayjs(e.end) > event.start
      );
      if( overlaping.length ) {
        // remove the event and do not go further
        this.cancelNewEvent();
        return false;
      }

      this.loading = true;
      this.showDialog = true;
      try {
        await this.$store.dispatch("loans/loadEmpty");
      } finally {
        this.loading = false;
      }

      this.newEvent = {...event, data: {
        status: 'creating',
        borrower: {user: this.user},
      }};
    },
    registerCancel (event, deleteEvent) {
      // register cancel method to use later when needed
      this.cancelNewEvent = deleteEvent;
      return true;
    },
    async askLoan() {

      await this.$store.dispatch("loans/test", {
        departure_at: this.$dayjs(this.newEvent.start).format("YYYY-MM-DD HH:mm:ss"),
        duration_in_minutes: this.$dayjs(this.newEvent.end).diff(this.newEvent.start, 'minutes'),
        estimated_distance:10,
        loanable_id:this.loanable.id,
      });

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
  .loanable-calendar__event--loan_in_process {
    background-color: $warning;
    border: 1px solid $warning;
  }
  .loanable-calendar__event--loan_completed {
    color: black;
    background-color: $success;
    border: 1px solid $success;
  }
  .loanable-calendar__event--loan_canceled {
    color: $danger;
    background-color: $danger;
    border: 1px solid $danger;
  }
}
#loanable-calendar-modal .modal-dialog .card.shadow {
  box-shadow: none !important;
}
</style>
