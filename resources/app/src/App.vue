<template>
  <div id="locomotion">
    <router-view />
  </div>
</template>

<script>
import Notification from '@/mixins/Notification';

export default {
  name: 'Locomotion',
  mixins: [Notification],
  async mounted() {
    await this.$store.dispatch('global/load');
  },
};
</script>

<style lang="scss">
</style>
