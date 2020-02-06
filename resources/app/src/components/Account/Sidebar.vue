<template>
  <ul class="sidebar">
    <b-nav-item to="/account">{{ $t('account') }}</b-nav-item>
    <b-nav-item to="/account/payments">{{ $t('payment') }}</b-nav-item>
    <b-nav-item to="/account/reservations">{{ $t('reservations') }}</b-nav-item>
    <b-nav-item to="/account/vehicles">{{ $t('vehicles') }}</b-nav-item>
  </ul>
</template>

<i18n>
fr:
  'account': Informations du compte
  'payment': Paiement
  'reservations': Historique des réservations
  'vehicles': Mes véhicules
en:
  'account': Account informations
  'payment': Payment
  'reservations': Reservations
  'vehicles': Vehicles
</i18n>

<style lang="scss">
.sidebar {
  list-style: none;
  padding: 0;
  margin: 0;

  .nav-item .nav-link {
    color: $black;
    padding-left: 0;

    &.disabled {
      color: $grey;
    }

    &.router-link-exact-active, &:hover {
      color: $locomotion-green;
    }
  }
}
</style>
