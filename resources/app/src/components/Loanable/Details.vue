<template>
  <div class="loanable-details">
    <header class="loanable-details__header">
      <img class="loanable-details__image" :src="loanableImage" :alt="loanableTitle" />
      <user-avatar :user="ownerUser" class="loanable-details__avatar" />
      <div class="loanable-details__tags">
        <div v-if="loanable.is_self_service">
          <b-badge variant="warning"> Libre service </b-badge>
        </div>
        <div v-else-if="!loanable.is_self_service">
          <b-badge variant="warning"> Sur demande </b-badge>
        </div>
      </div>
    </header>
    <main class="loanable-details__content">
      <div>
        <b-tabs content-class="mt-3" nav-wrapper-class="sticky-top bg-white">
          <b-tab title="Véhicule">
            <dl>
              <dt>Nom du véhicule</dt>
              <dd>{{ loanable.name }}</dd>

              <dt>Propriétaire</dt>
              <dd>{{ loanable.owner.user.full_name }}</dd>

              <div v-if="loanable.comments">
                <dt>Commentaires</dt>
                <dd>&laquo;&nbsp;{{ loanable.comments }}&nbsp;&raquo;</dd>
              </div>
            </dl>
          </b-tab>
          <b-tab title="Estimation">
            <div
              class="loanable-details__estimated-fare"
              v-if="loanable.price !== null && loanable.price !== undefined"
            >
              <table class="trip-details">
                <tr>
                  <th>Temps et distance&nbsp;:</th>
                  <td class="text-right tabular-nums">{{ loanable.price | currency }}</td>
                </tr>
                <tr v-if="loanable.insurance > 0">
                  <th>Assurances&nbsp;:</th>
                  <td class="text-right tabular-nums">{{ loanable.insurance | currency }}</td>
                </tr>
                <tr>
                  <th>Total&nbsp;:</th>
                  <td class="trip-details__total text-right tabular-nums">
                    {{ (loanable.price + loanable.insurance) | currency }}
                  </td>
                </tr>
              </table>
            </div>
            <div v-else class="loanable-details__estimated-fare">
              <p>
                Faites &laquo;&nbsp;valider la disponibilité&nbsp;&raquo; pour obtenir l'estimation.
              </p>
            </div>
          </b-tab>
        </b-tabs>
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

  .badge {
    padding: 0.5rem;
  }

  dt,
  dd {
    padding-left: 0.5rem;
  }

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
    height: 12rem;
    overflow-y: auto;
    padding: 0;
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
  &__image {
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
    position: absolute;
    bottom: 1rem;
    left: 1rem;
  }
  &__estimated-fare {
    text-align: center;
    font-size: 0.8rem;
    margin-bottom: 0.5rem;
  }
  // This is very similar to that in components/Loan/Actions/Payment.vue
  .trip-details {
    margin: 0 auto;

    th {
      text-align: left;
    }
    th,
    td {
      padding: 0 0.75rem;
    }
  }

  .trip-details__total {
    border-top: 1px solid black;
  }
}
</style>
