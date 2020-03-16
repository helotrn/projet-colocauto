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

const routeGuard = (to, from, next) => {
  if (to.name === 'profile') {
    next('/profile/account');
  } else {
    next();
  }
};

export default {
  name: 'Account',
  mixins: [Authenticated],
  components: {
    ProfileSidebar,
  },
  beforeRouteEnter: routeGuard,
  beforeRouteUpdate: routeGuard,
  computed: {
    pageTitle() {
      return this.$i18n.t(`profile.${this.$route.meta.title}`);
    },
  },
};
</script>

<style lang="scss">
.profile.page {
  .page__content {
    padding-top: 45px;
    padding-bottom: 45px;
  }
}
</style>
