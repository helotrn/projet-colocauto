<template>
  <header class="layout-header">
    <div class="layout-header__navbar">
      <layout-navbar :title="title" />
    </div>
  </header>
</template>

<script>
import LayoutNavbar from '@/components/Layout/Navbar.vue';

export default {
  name: 'Header',
  components: {
    LayoutNavbar,
  },
  props: {
    title: {
      type: String,
      require: false,
      default: '',
    },
  },
};
</script>

<style lang="scss">
@import "@/assets/scss/tools";

.layout-header {
  &__navbar {
    @include size("height", $layout-navbar-height-mobile, "", "", $layout-navbar-height);

    background-color: $eggshell;
    display: flex;
    flex-direction: column;
    justify-content: space-around;
  }
}
</style>
