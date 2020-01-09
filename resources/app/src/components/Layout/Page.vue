<template>
  <div :class="`page ${name}`">
    <layout-header class="page__header" />

    <b-container :fluid="wide" tag="main" class="page__content">
      <slot></slot>
    </b-container>

    <layout-footer class="page__footer" />
  </div>
</template>

<script>
export default {
  name: 'LayoutPage',
  props: {
    name: {
      type: String,
      required: true,
    },
    wide: {
      type: Boolean,
      required: false,
      default: false,
    },
  },
};
</script>

<style lang="scss">
</style>
