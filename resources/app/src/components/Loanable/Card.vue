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
