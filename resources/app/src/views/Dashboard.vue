<template>
  <div class="dashboard page" v-if="user">
    <layout-header class="page__header" />

    <main class="page__content">
      <h1>Bienvenue, {{ user.full_name }}</h1>
    </main>

    <layout-footer class="page__footer" />
  </div>
</template>

<script>
import Authenticated from '@/mixins/Authenticated';
import Notification from '@/mixins/Notification';

export default {
  name: 'Dashboard',
  mixins: [Authenticated, Notification],
  beforeMount() {
    if (!this.loggedIn) {
      this.skipToLogin('app');
    }
  }
};
</script>

<style lang="scss">
</style>
