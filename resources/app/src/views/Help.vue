<template>
  <layout-page name="help">
    <b-row tag="section" class="page__section">
      <b-col>
        <h1 class="page__section__title">
          Aide de locomotion
        </h1>
        <div class="page__section__content">
          <p>Des rubriques d'aide.</p>
        </div>
      </b-col>
    </b-row>
  </layout-page>
</template>

<script>
import Authenticated from '@/mixins/Authenticated';
import Notification from '@/mixins/Notification';

export default {
  name: 'Home',
  mixins: [Authenticated, Notification],
};
</script>

<style lang="scss">
</style>
