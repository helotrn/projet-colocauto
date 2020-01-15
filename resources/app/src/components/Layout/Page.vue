<template>
  <div :class="`page ${name}`">
    <layout-header class="page__header" />

    <b-container :fluid="fluid" tag="main" class="page__content" v-if="!wide">
      <slot />
    </b-container>
    <main class="page__content" v-else>
      <slot />
    </main>

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
    fluid: {
      type: Boolean,
      required: false,
      default: false,
    },
    wide: {
      type: Boolean,
      required: false,
      default: false,
    }
  },
};
</script>

<style lang="scss">
</style>
