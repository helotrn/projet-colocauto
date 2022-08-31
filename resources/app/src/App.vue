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
    if (this.$store.state.token && !this.$store.state.loading && !this.$store.state.loaded) {
      this.$store.dispatch("loadUser");
    }
  },
};
</script>

<style lang="scss"></style>
