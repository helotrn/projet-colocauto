<template>
  <div class="layout-loading">
    <img src="/loading.svg" />
  </div>
</template>

<script>
export default {
  name: "LayoutLoading",
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
