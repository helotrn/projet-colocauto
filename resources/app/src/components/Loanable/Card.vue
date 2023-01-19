<template>
  <b-card class="loanable-card" no-body>
    <div class="loanable-card__image">
      <img
        v-if="image"
        class="loanable-card__image__loanable loanable-card__image--custom"
        :src="this.image.sizes.thumbnail"
      />
      <div v-else class="loanable-card__image__loanable loanable-card__image--default">
        <svg-bike v-if="type == 'bike'" />
        <svg-car v-else-if="type == 'car'" />
        <svg-trailer v-else-if="type == 'trailer'" />
      </div>

      <div class="loanable-card__image__user" v-if="owner">
        <user-avatar :user="owner.user" variant="cut-out" />
      </div>
    </div>
    <div class="loanable-card__body">
      <h4 class="loanable-card__title">{{ name }}</h4>

      <loanable-calendar
        defaultView="week"
        :events="availability"
        variant="small"
        @ready="getAvailability"
        @view-change="getAvailability"
      ></loanable-calendar>
      
      <div class="loanable-card__tags">
        <div v-if="type === 'car'">
          <b-badge> <svg-car class="icon icon--as-text" /> Auto </b-badge>
        </div>
        <div v-else-if="type === 'bike'">
          <b-badge> <svg-bike class="icon icon--as-text" /> Vélo </b-badge>
        </div>
        <div v-else-if="type === 'trailer'">
          <b-badge> <svg-trailer class="icon icon--as-text" /> Remorque </b-badge>
        </div>

        <div v-if="isElectric">
          <img src="/icons/electric.png" />
        </div>
      </div>

      <div class="loanable-card__estimated-fare" v-if="estimatedCost">
        <i> Coût estimé: {{ estimatedCost.price | currency }} </i>
        <i v-if="estimatedCost.insurance">
          + Assurance: {{ estimatedCost.insurance | currency }}
        </i>
      </div>
      <div v-else class="loanable-card__estimated-fare">
        <i class="muted" title="Recherchez pour valider la disponibilité et le coût">
          Coût estimé: N/A
        </i>
      </div>

      <div class="loanable-card__buttons">
        <b-button variant="outline-primary" v-if="available" @click="$emit('select')">
          Demande d'emprunt
        </b-button>
        <b-button v-else-if="loading" variant="outline-warning" disabled>
          <b-spinner small v-if="loading" />
          Valider la disponibilité
        </b-button>
        <b-button
          v-else-if="!tested"
          variant="outline-warning"
          v-b-tooltip.hover
          :title="
            `Cliquez pour valider la disponibilité avec les paramètres ` + `d'emprunt sélectionnés`
          "
          :disabled="invalidDuration"
          @click.stop.prevent="
            loading = true;
            $emit('test');
          "
        >
          Valider la disponibilité
        </b-button>
        <b-button variant="outline-info" v-else disabled> Indisponible </b-button>
      </div>
    </div>
  </b-card>
</template>

<script>
import Vue from "vue";

import Bike from "@/assets/svg/bike.svg";
import Car from "@/assets/svg/car.svg";
import Trailer from "@/assets/svg/trailer.svg";

import UserAvatar from "@/components/User/Avatar.vue";
import LoanableCalendar from "@/components/Loanable/Calendar.vue";

export default {
  name: "LoanableCard",
  components: {
    UserAvatar,
    "svg-bike": Bike,
    "svg-car": Car,
    "svg-trailer": Trailer,
    LoanableCalendar,
  },
  props: {
    available: {
      type: Boolean,
      required: false,
      default: false,
    },
    bike_type: {
      type: String,
      required: false,
      default: null,
    },
    community: {
      type: Object,
      required: false,
      default: null,
    },
    engine: {
      type: String,
      required: false,
      default: null,
    },
    id: {
      type: Number,
      required: true,
    },
    image: {
      type: Object,
      required: false,
      default: null,
    },
    insurance: {
      type: Number,
      required: false,
      default: null,
    },
    name: {
      type: String,
      required: true,
    },
    owner: {
      type: Object,
      required: false,
      default: null,
    },
    estimatedCost: {
      type: Object,
      required: false,
      default: null,
    },
    tested: {
      type: Boolean,
      required: false,
      default: false,
    },
    type: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      availability: [],
    };
  },
  computed: {
    loading() {
      return this.$store.state.loans.cancelToken;
    },
    invalidDuration() {
      // Invalid if the duration of a loan is not greater than 0 minute.
      return !(this.$store.state.loans.item.duration_in_minutes > 0);
    },
    isElectric() {
      switch (this.type) {
        case "bike":
          return this.bike_type === "electric";
        case "car":
          return this.engine === "electric";
        default:
          return false;
      }
    },
  },
  methods: {
    getAvailability({ view, startDate, endDate, firstCellDate, lastCellDate, week }) {
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
        let load1 = Vue.axios
          .get(`/loanables/${this.id}/availability`, {
            params: {
              start: start.format("YYYY-MM-DD HH:mm:ss"),
              end: end.format("YYYY-MM-DD HH:mm:ss"),
              responseMode: "available",
            },
            cancelToken: cancelToken.token,
          });

        let load2 = Vue.axios
           .get(`/loanables/${this.id}/availability`, {
             params: {
               start: start.format("YYYY-MM-DD HH:mm:ss"),
               end: end.format("YYYY-MM-DD HH:mm:ss"),
              responseMode: "loans",
             },
             cancelToken: cancelToken.token,
           });

        Promise.all([load1, load2]).then(([response1, response2]) => {
          this.availability = response1.data.concat(response2.data);
        });
      } catch (e) {
        throw e;
      }
    },
  },
};
</script>

<style lang="scss">
@import "~bootstrap/scss/mixins/breakpoints";

.loanable-card {
  overflow: hidden;

  &__body {
    padding: 10px 20px 20px 20px;
  }

  &__tags {
    margin-bottom: 10px;

    svg path {
      fill: currentColor;
    }
  }

  &__estimated-fare {
    font-size: 10px;
    margin-bottom: 10px;
  }

  &__title,
  &__estimated-fare,
  &__tags,
  &__buttons {
    text-align: center;
  }

  &__image {
    position: relative;
    &--custom {
      width: 100%;
      aspect-ratio: 16 / 10;
      object-fit: cover;
    }

    &--default {
      fill: $locomotion-green;
      padding-top: 1rem;
      display: flex;
      align-items: center;

      svg {
        max-height: 115px;
        margin: 0 auto;
        display: block;
      }
    }

    &__user {
      position: absolute;
      bottom: 5%;
      right: 5%;
    }
  }
}
</style>
