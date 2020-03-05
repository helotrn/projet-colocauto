<template>
  <layout-page name="community-list">
    <div v-if="routeDataLoaded">
      <h1>Véhicules</h1>

      {{data}}
      <b-row>
        <b-col v-for="loanable in data" :key="loanable.id">
          <loanable-card v-bind="loanable" />
        </b-col>
      </b-row>
    </div>
    <layout-loading v-else />
  </layout-page>
</template>

<script>
import LoanableCard from '@/components/Loanable/Card.vue';

import DataRouteGuards from '@/mixins/DataRouteGuards';
import ListMixin from '@/mixins/ListMixin';

export default {
  name: 'CommunityList',
  mixins: [DataRouteGuards, ListMixin],
  components: { LoanableCard },
};
</script>

<style lang="scss">
.community-list {
  .page__content {
    padding-top: 45px;
    padding-bottom: 45px;
  }
}
</style>
