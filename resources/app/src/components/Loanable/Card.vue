<template>
  <b-card class="loanable-card mb-3" no-body>
    <div class="loanable-card__image">
      <div class="loanable-card__image__user" :style="userAvatarStyle" />

      <div class="loanable-card__image__loanable">
        <div :style="loanableImageStyle" />
      </div>
    </div>

    <h2 class="loanable-card__title">{{ name }}</h2>

    <div class="loanable-card__tags">
      <div v-if="type === 'car'">
        <b-badge>
          <svg-car /> Voiture
        </b-badge>
      </div>
      <div v-else-if="type === 'bike'">
        <b-badge>
          <svg-bike /> Vélo
        </b-badge>
      </div>
      <div v-else-if="type === 'trailer'">
        <b-badge>
          <svg-trailer /> Remorque
        </b-badge>
      </div>

      <div v-if="isElectric">
        <img src="/icons/electric.png">
      </div>
    </div>

    <div class="loanable-card__estimated-fare" v-if="price">
      <i v-b-tooltip.hover :title="pricing">
        Coût estimé: {{ price | currency }}
      </i>
    </div>
    <div v-else class="loanable-card__estimated-fare">
      <i class="muted" v-b-tooltip.hover
        title="Recherchez pour valider la disponibilité et le coût">
        Coût estimé: N/A
      </i>
    </div>

    <div class="loanable-card__buttons">
      <b-button variant="outline-primary" v-if="available">
        Demande d'emprunt
      </b-button>
      <i v-else-if="!tested" v-b-tooltip.hover
        title="Recherchez pour valider la disponibilité et le coût">
        <b-button variant="outline-primary" disabled>
          Demande d'emprunt
        </b-button>
      </i>
      <b-button variant="outline-info" v-else disabled>
        Indisponible
      </b-button>
    </div>
  </b-card>
</template>

<script>
import Bike from '@/assets/svg/bike.svg';
import Car from '@/assets/svg/car.svg';
import Trailer from '@/assets/svg/trailer.svg';

export default {
  name: 'LoanableCard',
  components: {
    'svg-bike': Bike,
    'svg-car': Car,
    'svg-trailer': Trailer,
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
    name: {
      type: String,
      required: true,
    },
    owner: {
      type: Object,
      required: false,
      default: null,
    },
    price: {
      type: Number,
      required: false,
      default: null,
    },
    pricing: {
      type: String,
      required: false,
      default: '',
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
    isElectric() {
      switch (this.type) {
        case 'bike':
          return this.bike_type === 'electric';
        case 'car':
          return this.engine === 'electric';
        default:
          return false;
      }
    },
    userAvatarStyle() {
      if (!this.owner.user || !this.owner.user.avatar) {
        return {};
      }

      return {
        backgroundImage: `url('${this.owner.user.avatar.sizes.thumbnail}')`,
      };
    },
    loanableImageStyle() {
      if (!this.image) {
        return {};
      }

      return {
        backgroundImage: `url('${this.image.sizes.thumbnail}')`,
      };
    },
  },
};
</script>

<style lang="scss">
.loanable-card {
  padding: 20px;

  &__title {
    font-size: 20px;
  }

  &__tags {
    margin-bottom: 10px;
  }

  &__estimated-fare {
    font-size: 10px;
    margin-bottom: 10px;
  }

  &__title, &__estimated-fare, &__tags, &__buttons {
    text-align: center;
  }

  &__image {
    height: 115px;
    position: relative;
    width: 100%;

    &__user, &__loanable > div {
      background-size: cover;
      background-position: center center;
      background-repeat: no-repeat;
      border-radius: 100%;
    }

    &__user {
      height: 100%;
      width: 115px;
      margin: 0 auto;
    }

    &__loanable {
      position: absolute;
      bottom: 0;
      left: calc(50% + 30px);
      height: 50%;
      width: 115px;

      > div {
        width: calc(115px / 2);
        height: calc(115px / 2);
      }
    }
  }
}
</style>
