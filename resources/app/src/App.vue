<template>
  <div id="locomotion">
    <router-view />
  </div>
</template>

<script>
import Notification from "@/mixins/Notification";

export default {
  name: "LocoMotion",
  mixins: [Notification],
  mounted() {
    this.$store.dispatch('loadUser');
  }
};
</script>

<style lang="scss"></style>
