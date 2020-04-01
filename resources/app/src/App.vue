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
};
</script>

<style lang="scss">
</style>
