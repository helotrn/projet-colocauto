<template>
  <b-card class="loanable-card" no-body>
    <div class="loanable-card__image">
      <div class="loanable-card__image__user" :style="userAvatarStyle" />

      <div class="loanable-card__image__loanable" :style="loanableImageStyle" />
    </div>

    <h2>{{ name }}</h2>

    <div class="loanable-card__tags"/>

    <div class="loanable-card__estimated-fare"/>

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
        backgroundImage: `url('${this.owner.user.avatar.sizes.thumbnail.url}')`,
      };
    },
    loanableImageStyle() {
      if (!this.image) {
        return {};
      }

      return {
        backgroundImage: `url('${this.image.sizes.thumbnail.url}')`,
      };
    },
  },
};
</script>

<style lang="scss">
</style>
