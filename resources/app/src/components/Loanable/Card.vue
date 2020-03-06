<template>
  <b-card class="loanable-card" no-body>
    <div class="loanable-card__image">
      <div class="loanable-card__image__user" :style="userAvatarStyle" />

      <div class="loanable-card__image__loanable">
        <div :style="loanableImageStyle" />
      </div>
    </div>

    <h2 class="loanable-card__title">{{ name }}</h2>

    <div class="loanable-card__tags">
      <div v-if="type === 'car'">
        Voiture
      </div>
      <div v-else-if="type === 'bike'">
        Vélo
      </div>
      <div v-else-if="type === 'trailer'">
        Remorque
      </div>
    </div>

    <div class="loanable-card__estimated-fare">Coût estimé: {{ estimatedFare || 'N/A' }}</div>

    <div class="loanable-card__buttons">
      <b-button variant="outline-primary">Demande d'emprunt</b-button>
    </div>
  </b-card>
</template>

<script>
export default {
  name: 'LoanableCard',
  props: {
    community: {
      type: Object,
      required: false,
      default: null,
    },
    estimatedFare: {
      type: Number,
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
  },
  computed: {
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

    &__user, &__loanable {
      position: absolute;
      height: 100%;
      width: 115px;
      bottom: 0;
    }

    &__user, &__loanable > div {
      border-radius: 100%;
      background-size: cover;
      background-position: center center;
      background-repeat: no-repeat;
    }

    &__user {
      left: 50%;
      margin-left: -25%;
      background-color: $white;
    }

    &__loanable {
      left: 50%;
      margin-left: -15%;

      > div {
        width: 57.5px;
        height: 50%;
        margin-left: 50%;
        margin-top: 50%;
        background-color: $white;
        border-radius: 100%;
      }
    }
  }
}
</style>
