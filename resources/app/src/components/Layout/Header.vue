<template>
  <header class="layout-header">
    <div class="layout-header__navbar">
      <layout-navbar :title="title" />
    </div>
    <div class="layout-header__communities-navbar">
      <layout-communities-navbar />
    </div>
  </header>
</template>

<script>
import LayoutNavbar from "@/components/Layout/Navbar.vue";
import LayoutCommunitiesNavbar from "@/components/Layout/CommunitiesNavbar.vue";

export default {
  name: "Header",
  components: {
    LayoutNavbar,
    LayoutCommunitiesNavbar,
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
