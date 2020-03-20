<template>
  <div class="dashboard-balance">
    <h3>Mon solde</h3>

    <div class="dashboard-balance__balance">
      {{ roundedBalance }}&nbsp;$
    </div>

    <div class="dashboard-balance__buttons">
      <b-button class="mr-3" size="sm" variant="primary">
        {{ $t('approvisionner') }}
      </b-button>

      <span v-if="user.balance < 10"
        tabindex="0"  v-b-tooltip.hover :title="$t('reclamer_tooltip')">
        <b-button size="sm" variant="outline-primary" disabled>
          {{ $t('reclamer') }}
        </b-button>
      </span>
      <b-button v-else size="sm" variant="outline-primary">
        {{ $t('reclamer') }}
      </b-button>
    </div>

    <p class="dashboard-balance__payment-methods">
      <router-link to="/profile/payment_methods">
        {{ $t('modify_payment_method') }}
      </router-link>
    </p>
  </div>
</template>

<i18n>
fr:
  approvisionner: Approvisionner
  modify_payment_method: Modifier les méthodes de paiement
  reclamer: Réclamer
  reclamer_tooltip: Un minimum de 10$ est requis pour réclamer son solde.
</i18n>

<script>
export default {
  name: 'DashboardBalance',
  props: {
    user: {
      type: Object,
      required: true,
    },
  },
  computed: {
    roundedBalance() {
      return Math.floor(this.user.balance);
    },
  },
};
</script>

<style lang="scss">
.dashboard-balance {
  h3 {
    font-size: 20px;
    font-weight: normal;
    font-weight: 600;
  }

  &__balance {
    font-size: 40px;
    font-weight: 500;
    margin-bottom: 15px;
  }

  &__buttons {
    margin-bottom: 10px;
  }

  &__payment-methods {
    font-size: 13px;
  }
}
</style>
