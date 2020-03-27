<template>
  <div class="profile-loans" v-if="loaded">
    <div class="profile-loans__loans" v-if="data.length > 0">
      <div class="profile-loans__loans__loan"
        v-for="loan in data" :key="loan.id">
        <loan-info-box :loan="loan" :buttons="['view']" :user="user" with-steps />
      </div>
    </div>
    <div v-else>
      <p>
        Aucun emprunt.
      </p>
    </div>
  </div>
  <layout-loading v-else />
</template>

<script>
import LoanInfoBox from '@/components/Loan/InfoBox.vue';

import Authenticated from '@/mixins/Authenticated';
import DataRouteGuards from '@/mixins/DataRouteGuards';
import ListMixin from '@/mixins/ListMixin';

export default {
  name: 'ProfileLoans',
  mixins: [Authenticated, DataRouteGuards, ListMixin],
  components: { LoanInfoBox },
};
</script>

<style lang="scss">
</style>
