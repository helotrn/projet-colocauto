<template>
  <layout-page name="community" wide>
    <router-view />
  </layout-page>
</template>

<script>
import Authenticated from '@/mixins/Authenticated';
import Notification from '@/mixins/Notification';

export default {
  name: 'Community',
  mixins: [Authenticated, Notification],
};
</script>

<style lang="scss">
.page.community {
  main {
    background-color: $locomotion-green;
    display: flex;
    flex-direction: column;
    justify-content: space-around;
  }
}
</style>
