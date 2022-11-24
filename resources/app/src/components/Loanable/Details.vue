<template>
  <div class="loanable-details">
    <header class="loanable-details__header">
      <img
        v-if="loanableImage"
        class="loanable-details__image loanable-details__image--custom"
        :src="loanableImage"
        :alt="loanableTitle"
      />
      <svg-bike
        v-else-if="loanable.type == 'bike'"
        class="loanable-details__image loanable-details__image--default"
      />
      <svg-car
        v-else-if="loanable.type == 'car'"
        class="loanable-details__image loanable-details__image--default"
      />
      <svg-trailer
        v-else-if="loanable.type == 'trailer'"
        class="loanable-details__image loanable-details__image--default"
      />

      <user-avatar :user="ownerUser" variant="cut-out" class="loanable-details__avatar" />
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
        <b-tabs nav-wrapper-class="sticky-top bg-white">
          <b-tab class="mt-3" title="Véhicule">
            <dl>
              <dt>Nom du véhicule</dt>
              <dd>{{ loanable.name }}</dd>

              <dt>Propriétaire</dt>
              <dd>{{ loanable.owner.user.full_name }}</dd>

              <template v-if="loanable.comments">
                <dt>Commentaires</dt>
                <dd>&laquo;&nbsp;{{ loanable.comments }}&nbsp;&raquo;</dd>
              </template>
              <template v-if="loanable.type === 'bike'">
                <dt>{{ $t("fields.model") | capitalize }}</dt>
                <dd>{{ loanable.model }}</dd>
                <dt>{{ $t("fields.bike_type") | capitalize }}</dt>
                <dd>{{ $t(`bike_types.${loanable.bike_type}`) | capitalize }}</dd>
                <dt>{{ $t("fields.size") | capitalize }}</dt>
                <dd>
                  {{ $t(`sizes.${loanable.size}`) | capitalize }}
                </dd>
              </template>
              <template v-else-if="loanable.type === 'trailer'">
                <dt>{{ $t("fields.maximum_charge") | capitalize }}</dt>
                <dd>{{ loanable.maximum_charge }}</dd>
                <dt>{{ $t("fields.dimensions") | capitalize }}</dt>
                <dd>{{ loanable.dimensions }}</dd>
              </template>
              <template v-else-if="loanable.type === 'car'">
                <dt>{{ $t("fields.brand") | capitalize }}</dt>
                <dd>{{ loanable.brand }}</dd>
                <dt>{{ $t("fields.model") | capitalize }}</dt>
                <dd>{{ loanable.model }}</dd>
                <dt>{{ $t("fields.year_of_circulation") | capitalize }}</dt>
                <dd>{{ loanable.year_of_circulation }}</dd>
                <dt>{{ $t("fields.transmission_mode") | capitalize }}</dt>
                <dd>
                  {{ $t(`transmission_modes.${loanable.transmission_mode}`) | capitalize }}
                </dd>
                <dt>{{ $t("fields.engine") | capitalize }}</dt>
                <dd>{{ $t(`engines.${loanable.engine}`) | capitalize }}</dd>
                <dt>{{ $t("fields.papers_location") | capitalize }}</dt>
                <dd>
                  {{ $t(`papers_locations.${loanable.papers_location}`) | capitalize }}
                </dd>
              </template>
            </dl>
          </b-tab>
          <b-tab class="mt-3" title="Estimation">
            <div class="loanable-details__estimated-fare" v-if="loanable.estimatedCost">
              <table class="trip-details">
                <tr>
                  <th>Temps et distance&nbsp;:</th>
                  <td class="text-right tabular-nums">
                    {{ loanable.estimatedCost.price | currency }}
                  </td>
                </tr>
                <tr v-if="loanable.estimatedCost.insurance > 0">
                  <th>Assurances&nbsp;:</th>
                  <td class="text-right tabular-nums">
                    {{ loanable.estimatedCost.insurance | currency }}
                  </td>
                </tr>
                <tr>
                  <th>Total&nbsp;:</th>
                  <td class="trip-details__total text-right tabular-nums">
                    {{
                      (loanable.estimatedCost.price + loanable.estimatedCost.insurance) | currency
                    }}
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

import locales from "@/locales";

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
  i18n: {
    messages: {
      en: {
        ...locales.en.loanables,
      },
      fr: {
        ...locales.fr.loanables,
      },
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
    max-height: 12rem;
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
  &__image--custom {
    object-fit: cover;
  }
  &__image--default {
    fill: $locomotion-green;
    position: relative;
    height: 8rem;
    top: 1rem;
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
