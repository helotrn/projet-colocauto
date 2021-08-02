<template>
  <router-link :to="to" :class="`tutorial-block variant-${variant}`" :style="{ backgroundImage }">
    <h2>{{ title }}</h2>
  </router-link>
</template>

<script>
export default {
  name: "TutorialBlock",
  props: {
    bgImage: {
      type: String,
      required: false,
      default: null,
    },
    to: {
      type: String,
      required: true,
    },
    title: {
      type: String,
      required: true,
    },
    variant: {
      type: String,
      required: false,
      default: "light",
    },
  },
  computed: {
    backgroundImage() {
      if (this.bgImage) {
        return `url('${this.bgImage}')`;
      }

      return undefined;
    },
  },
};
</script>

<style lang="scss">
.tutorial-block {
  min-height: 210px;
  width: 100%;
  border-radius: 15px;
  margin-bottom: 30px;
  padding: 30px;

  display: block;

  background-repeat: no-repeat;
  background-size: cover;
  background-position: center right;

  &:hover,
  &:active,
  &:focus {
    text-decoration: none;
  }

  h2 {
    font-size: 26px;
    font-weight: bold;
    color: $white;
  }

  &.variant {
    &-light {
      background-color: $locomotion-light-green;
    }

    &-dark {
      background-color: $locomotion-dark-green;
    }
  }
}
</style>
