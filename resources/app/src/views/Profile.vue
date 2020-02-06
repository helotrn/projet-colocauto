<template>
  <layout-page name="profile">
    <h1 class="page__title">
      {{ pageTitle | capitalize }}
    </h1>

    <b-row>
      <b-col md="4" lg="3">
        <profile-sidebar />
      </b-col>

      <b-col md="8" lg="9">
        <router-view />
      </b-col>
    </b-row>
  </layout-page>
</template>

<script>
import ProfileSidebar from '@/components/Profile/Sidebar.vue';

import Authenticated from '@/mixins/Authenticated';
import Notification from '@/mixins/Notification';

const routeGuard = (to, from, next) => {
  if (to.name === 'profile') {
    next('/profile/account');
  } else {
    next();
  }
};

export default {
  name: 'Account',
  mixins: [Authenticated, Notification],
  components: {
    ProfileSidebar,
  },
  beforeRouteEnter: routeGuard,
  beforeRouteUpdate: routeGuard,
  computed: {
    pageTitle() {
      switch (this.$route.name) {
        case 'payments':
          return this.$i18n.t('profile.titles.payment');
        case 'account':
        default:
          return this.$i18n.t('profile.titles.account');
      }
    }
  }
};
</script>

<style lang="scss">
</style>
