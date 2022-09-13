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
import Bike from "@/assets/svg/bike.svg";
import Car from "@/assets/svg/car.svg";
import Trailer from "@/assets/svg/trailer.svg";

import UserAvatar from "@/components/User/Avatar.vue";

export default {
  name: "LoanableCard",
  components: {
    UserAvatar,
    "svg-bike": Bike,
    "svg-car": Car,
    "svg-trailer": Trailer,
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
    height: 115px;
    position: relative;
    &--custom {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    &--default {
      fill: $locomotion-green;
      padding-top: 1rem;
      height: 100%;

      svg {
        height: 100%;
        margin: 0 auto;
        display: block;
      }
    }

    &__user {
      position: absolute;
      bottom: 1rem;
      right: 1rem;
    }
  }
}
</style>
