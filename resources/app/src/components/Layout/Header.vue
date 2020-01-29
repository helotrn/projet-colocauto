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
  .layout-header__navbar {
    background-color: $eggshell;
  }
</style>
