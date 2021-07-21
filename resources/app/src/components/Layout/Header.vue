<template>
  <header class="layout-header">
    <div class="layout-header__navbar">
      <layout-navbar :title="title" />
    </div>
  </header>
</template>

<script>
import LayoutNavbar from "@/components/Layout/Navbar.vue";

export default {
  name: "Header",
  components: {
    LayoutNavbar,
  },
  props: {
    title: {
      type: String,
      require: false,
      default: "",
    },
  },
};
</script>

<style lang="scss"></style>
