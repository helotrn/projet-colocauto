<template>
  <div class="dashboard-loan-history">
    <h3>Historique d'emprunts</h3>

    <div v-if="loans.length > 0" class="dashboard-loan-history__loans">
      <ul class="dashboard-loan-history__loans"
        v-for="loan in loans" :key="loan.id">
        <li class="dashboard-loan-history__loans__loan">
          {{ loan.loanable.name }}<br>
          <span>{{ loan.departure_at | date }}</span><br>
          <span v-if="loan.borrower.id === borrower.id">
            Payé {{ loan.final_price | currency }}
          </span>
          <span v-else>
            Reçu {{ loan.final_price | currency }}
          </span>
        </li>
      </ul>
    </div>
    <div v-else class="dashboard-loan-history__loans">
      <p class="muted">Aucun historique d'emprunt.</p>
    </div>

    <p class="dashboard-loan-history__link">
      <router-link to="/profile/loans">Voir tout l'historique</router-link>
    </p>
  </div>
</template>

<script>
export default {
  name: 'DashboardLoanHistory',
  props: {
    borrower: {
      type: Object,
      required: true,
    },
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

  &__loans {
    list-style-type: none;
    margin: 0;
    padding: 0;
    margin-bottom: 1rem;
  }
}
</style>
