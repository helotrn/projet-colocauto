<template>
  <ul class="profile-sidebar">
    <b-nav-item to="/profile/account">
      {{ $t('profile.titles.account') }}
    </b-nav-item>
    <b-nav-item to="/profile/payments" :disabled="!user.approved_at">
      {{ $t('profile.titles.payment') }}
    </b-nav-item>
    <b-nav-item to="/profile/reservations" :disabled="!user.approved_at">
      {{ $t('profile.titles.reservations') }}
    </b-nav-item>
    <b-nav-item to="/profile/vehicles" :disabled="!user.approved_at">
      {{ $t('profile.titles.vehicles') }}
    </b-nav-item>
  </ul>
</template>

<script>
import Authenticated from '@/mixins/Authenticated';
import Notification from '@/mixins/Notification';

export default {
  name: 'ProfileSidebar',
  mixins: [Authenticated, Notification],
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
