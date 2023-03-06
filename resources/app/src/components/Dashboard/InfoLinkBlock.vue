<template>
  <div class="card mb-2">
    <component
      :is="isRouter ? 'routerLink' : 'a'"
      :to="to"
      :href="isRouter ? undefined : to"
      class="d-flex align-items-center"
    >
      <slot></slot>
      <img v-if="img" class="p-2" :src="img" />
      <span class="text-dark pr-3"> {{ title }}</span>
    </component>
  </div>
</template>

<script>
export default {
  name: "InfoLinkBlock",
  props: {
    title: {
      type: String,
      required: true,
    },
    to: {
      type: String,
      required: true,
    },
    img: {
      type: String,
      required: false,
    },
  },
  computed: {
    isRouter() {
      return !this.to.match(/^(http|mailto)/);
    }
  }
}
</script>

<style scoped lang="scss">
  .card > a {
    font-size: 20px;
  }
</style>
