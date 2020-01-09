<template>
  <div class="dashboard page" v-if="user">
    <layout-header class="page__header" />

    <b-container tag="main" class="page__content">
      <b-row tag="section" class="page__sectin">
        <b-col class="page__content__main" lg="9">
          <h1>Bienvenue, {{ user.full_name }}</h1>
        </b-col>

        <b-col class="page__content__sidebar" lg="3">
        </b-col>
      </b-row>
    </b-container>

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
