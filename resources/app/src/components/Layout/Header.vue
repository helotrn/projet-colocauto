<template>
  <header class="layout-header">
    <div class="layout-header__navbar">
      <layout-navbar :title="title" />
    </div>
    <div v-if="!isAdmin" class="layout-header__communities-navbar">
      <layout-communities-navbar />
    </div>
  </header>
</template>

<script>
import LayoutNavbar from "@/components/Layout/Navbar.vue";
import LayoutCommunitiesNavbar from "@/components/Layout/CommunitiesNavbar.vue";
import UserMixin from "@/mixins/UserMixin";

export default {
  name: "Header",
  mixins: [UserMixin],
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
