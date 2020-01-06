<template>
  <header class="layout-header">
    <div class="layout-header__navbar">
      <layout-navbar />
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
};
</script>

<style lang="scss">
.layout-header {
  &__navbar {
    height: $layout-navbar-height;
    background-color: $eggshell;
    display: flex;
    flex-direction: column;
    justify-content: space-around;
  }
}
</style>
