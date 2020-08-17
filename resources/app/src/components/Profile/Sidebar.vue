<template>
  <ul class="profile-sidebar" v-if="user">
    <b-nav-item to="/profile/locomotion">
      {{ $t('profile.titles.profile') }}
    </b-nav-item>
    <b-nav-item to="/profile/account">
      {{ $t('profile.titles.account') }}
    </b-nav-item>
    <b-nav-item to="/profile/communities">
      {{ $t('profile.titles.communities') }}
    </b-nav-item>
    <b-nav-item to="/profile/borrower">
      {{ $t('profile.titles.borrower') }}
    </b-nav-item>
    <b-nav-item to="/profile/loans" :disabled="!user.borrower">
      {{ $t('profile.titles.loans') }}
    </b-nav-item>
    <b-nav-item to="/profile/loanables">
      {{ $t('profile.titles.loanables') }}
    </b-nav-item>
    <b-nav-item to="/profile/payment_methods" :disabled="!hasCompletedRegistration">
      {{ $t('profile.titles.payment_methods') }}
    </b-nav-item>
    <b-nav-item to="/profile/invoices" :disabled="!hasCompletedRegistration">
      {{ $t('profile.titles.invoices') }}
    </b-nav-item>
  </ul>
</template>

<script>
import Authenticated from '@/mixins/Authenticated';

export default {
  name: 'ProfileSidebar',
  mixins: [Authenticated],
};
</script>

<style lang="scss">
.profile-sidebar {
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
