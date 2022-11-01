<template>
  <div
    :class="{
      'layout-loading': true,
      'inline-with-buttons': withButton,
      'inline-with-text': withText,
    }"
  >
    <img src="/loading.svg" />
  </div>
</template>

<script>
export default {
  name: "LayoutLoading",
  props: {
    withButton: {
      type: Boolean,
      default: false,
    },
    withText: {
      type: Boolean,
      default: false,
    },
  },
};
</script>

<style lang="scss">
.layout-loading {
  display: flex;
  justify-content: center;
  text-align: center;
  &.inline-with-buttons {
    display: inline-block;
    img {
      height: 30px;
      width: 30px;
    }
  }
  &.inline-with-text {
    display: inline-block;
    img {
      height: 1rem;
      width: 1rem;
    }
  }
}
</style>
