<template>
  <div class="loanable-details">
    <header class="loanable-details__header">
      <img class="loanable-details-image" :src="loanableImage" :alt="loanableTitle" />
      <user-avatar :user="ownerUser" class="loanable-details__avatar" />
    </header>
    <main class="loanable-details__content">
      <h4 class="loanable-details__loanable-title">{{ loanableTitle }}</h4>

      <div class="loanable-details__tags">
        <div v-if="loanable.type === 'car'">
          <b-badge> <svg-car /> Auto </b-badge>
        </div>
        <div v-else-if="loanable.type === 'bike'">
          <b-badge> <svg-bike /> Vélo </b-badge>
        </div>
        <div v-else-if="loanable.type === 'trailer'">
          <b-badge> <svg-trailer /> Remorque </b-badge>
        </div>
      </div>

      <div
        class="loanable-details__estimated-fare"
        v-if="loanable.price !== null && loanable.price !== undefined"
      >
        <i> Coût estimé: {{ loanable.price | currency }} </i>
        <i v-if="loanable.insurance"> + Assurance: {{ loanable.insurance | currency }} </i>
      </div>
      <div v-else class="loanable-details__estimated-fare">
        <i class="muted" title="Recherchez pour valider la disponibilité et le coût">
          Coût estimé: N/A
        </i>
      </div>
    </main>
    <footer class="loanable-details__footer">
      <!-- Only one button will be displayed at a time. -->
      <b-button variant="outline-primary" v-if="loanable.available" @click="$emit('select')">
        Demande d'emprunt
      </b-button>
      <b-button v-else-if="loading" variant="outline-warning" disabled>
        <b-spinner small v-if="loading" />
        Valider la disponibilité
      </b-button>
      <b-button
        v-else-if="!loanable.tested"
        variant="outline-warning"
        v-b-tooltip.hover
        :title="
          `Cliquez pour valider la disponibilité avec les paramètres ` + `d'emprunt sélectionnés`
        "
        :disabled="invalidDuration"
        @click.stop.prevent="$emit('test')"
      >
        Valider la disponibilité
      </b-button>
      <b-button variant="outline-info" v-else disabled> Indisponible </b-button>
    </footer>
  </div>
</template>

<script>
import Bike from "@/assets/svg/bike.svg";
import Car from "@/assets/svg/car.svg";
import Trailer from "@/assets/svg/trailer.svg";

import UserAvatar from "@/components/User/Avatar.vue";

export default {
  name: "LoanableDetails",
  components: {
    "svg-bike": Bike,
    "svg-car": Car,
    "svg-trailer": Trailer,
    UserAvatar,
  },
  props: {
    loanable: {
      type: Object,
      required: false,
      default: null,
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
    loanableTitle() {
      return this?.loanable?.name;
    },
    loanableImage() {
      return this?.loanable?.image?.sizes?.thumbnail;
    },
    ownerUser() {
      return this?.loanable?.owner?.user;
    },
  },
};
</script>

<style lang="scss">
.loanable-details {
  // Fixed width for the moment. We'll deal with resizing later.
  width: 16rem;

  &__header {
    position: relative;
    // At the moment, thumbnails are 256px x 160px -> 16rem x 10rem.
    height: 10rem;
    width: 100%;
    overflow-y: hidden;
  }
  &__avatar {
    position: absolute;
    bottom: 1rem;
    right: 1rem;
  }
  &__content {
    max-height: 12rem;
    overflow-y: auto;
    padding: 0.5rem;
  }
  &__footer {
    height: 3.5rem;
    padding: 0.5rem;
    // To get past the arrow that display over the button.
    padding-bottom: 0.75rem;

    display: flex;
    justify-content: space-around;
    align-items: center;
  }
  &__loanable-image {
    position: relative;
    height: 10rem;
    width: 100%;
  }
  &__owner-avatar {
    position: absolute;
    width: 4rem;
    height: 4rem;
    bottom: 0;
    right: 0;
  }
  /* Temporary element until we create sections. */
  &__loanable-title {
    text-align: center;
  }
  &__tags {
    text-align: center;
    margin-bottom: 0.5rem;
  }
  &__estimated-fare {
    text-align: center;
    font-size: 0.8rem;
    margin-bottom: 0.5rem;
  }
}
</style>
