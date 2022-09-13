<template>
  <div :class="{ 'layout-loading': true, 'inline-with-buttons': inline }">
    <img src="/loading.svg" />
  </div>
</template>

<script>
export default {
  name: "LayoutLoading",
  props: {
    inline: {
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
}
</style>
