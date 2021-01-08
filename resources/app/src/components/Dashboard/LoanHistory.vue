<template>
  <div class="dashboard-loan-history">
    <h3>{{ widgetTitle }}
      <b-nav-item-dropdown right tag="h3" text="" v-model="loanHistoryType">
        <b-dropdown-item :active="loanHistoryType === 'past'"
          @click="loanHistoryType = 'past'">
          Passés
        </b-dropdown-item>
        <b-dropdown-item :active="loanHistoryType === 'ongoing'"
          @click="loanHistoryType = 'ongoing'">
          En cours
        </b-dropdown-item>
        <b-dropdown-item :active="loanHistoryType === 'upcoming'"
          @click="loanHistoryType = 'upcoming'">
          À venir
        </b-dropdown-item>
        <b-dropdown-item :active="loanHistoryType === 'waiting'"
          @click="loanHistoryType = 'waiting'">
          Nouveaux
        </b-dropdown-item>
      </b-nav-item-dropdown>
    </h3>

    <div v-if="loans.length > 0" class="dashboard-loan-history__loans">
      <ul class="dashboard-loan-history__loans"
        v-for="loan in loans" :key="loan.id">
        <li class="dashboard-loan-history__loans__loan">
          {{ loan.loanable.name }}<br>
          <span>{{ loan.departure_at | date }}</span><br>
          <span v-if="loan.borrower.id === borrower.id">
            Payé {{ loan.total_final_cost | currency }}
          </span>
          <span v-else>
            Reçu {{ (loan.final_price - loan.final_purchases_amount) | currency }}
          </span>
        </li>
      </ul>
    </div>
    <div v-else class="dashboard-loan-history__loans">
      <p class="muted">Aucun trajet.</p>
    </div>

    <p class="dashboard-loan-history__link">
      <router-link to="/profile/loans">Voir tous les trajets</router-link>
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
    ongoingLoans: {
      type: Array,
      required: true,
    },
    pastLoans: {
      type: Array,
      required: true,
    },
    upcomingLoans: {
      type: Array,
      required: true,
    },
    waitingLoans: {
      type: Array,
      required: true,
    },
  },
  data() {
    return {
      loanHistoryType: 'past',
    };
  },
  computed: {
    loans() {
      switch (this.loanHistoryType) {
        case 'upcoming':
          return this.upcomingLoans;
        case 'waiting':
          return this.waitingLoans;
        case 'ongoing':
          return this.ongoingLoans;
        case 'past':
        default:
          return this.pastLoans;
      }
    },
    widgetTitle() {
      switch (this.loanHistoryType) {
        case 'upcoming':
          return 'Trajets à venir';
        case 'waiting':
          return 'Nouveaux trajets';
        case 'ongoing':
          return 'Trajets en cours';
        case 'past':
          return 'Trajets passés';
        default:
          return 'Mes trajets';
      }
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

    display: flex;

    li {
      style-type: none;
      display: inline;
      flex: 0 1 25px;

      > a.nav-link {
        color: $black;
        padding: 0;
      }
    }
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
