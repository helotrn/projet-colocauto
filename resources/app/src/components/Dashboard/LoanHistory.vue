<template>
  <div class="dashboard-loan-history">
    <h3>Historique d'emprunts</h3>

    <div v-if="loans.length > 0" class="dashboard-loan-history__loans">
      <div class="dashboard-loan-history__loans__loan"
        v-for="loan in loans" :key="loan.id">
        {{ loan }}
      </div>
    </div>
    <div v-else class="dashboard-loan-history__loans">
      <p class="muted">Aucun historique d'emprunt.</p>
    </div>

    <p class="dashboard-loan-history__link">
      <router-link to="/profile/loans">Voir l'historique</router-link>
    </p>
  </div>
</template>

<script>
export default {
  name: 'DashboardLoanHistory',
  props: {
    loans: {
      type: Array,
      required: true,
    },
  },
};
</script>

<style lang="scss">
.dashboard-loan-history {
  h3 {
    font-size: 20px;
    font-weight: normal;
    font-weight: 600;
  }

  &__loans, &__link {
    font-size: 13px;
  }
}
</style>
