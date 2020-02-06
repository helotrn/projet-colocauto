<template>
  <layout-page name="account">
    <h1 class="page__title">
      {{ $t('title') }}
    </h1>

    <b-row>
      <b-col md="4" lg="3">
        <sidebar />
      </b-col>
      <b-col md="8" lg="9">
        <profile-form />
      </b-col>
    </b-row>

  </layout-page>
</template>

<i18n>
fr:
  'title': Paramètres du compte
en:
  'title': Account settings
</i18n>

<script>
import Authenticated from '@/mixins/Authenticated';
import Notification from '@/mixins/Notification';

import ProfileForm from '@/components/Account/ProfileForm.vue';
import Sidebar from '@/components/Account/Sidebar.vue';

export default {
  name: 'Account',
  mixins: [Authenticated, Notification],
  components: {
    ProfileForm,
    Sidebar,
  },
  beforeMount() {
    if (!this.loggedIn) {
      this.skipToLogin('app');
    }
  },
};
</script>

<style lang="scss">
@import "@/assets/scss/tools";

.page.account {
  .page__content {
    padding-top: 50px;
  }

  .page__title {
    @include space("margin-bottom", 40px, "", "", 66px);
  }
}
</style>
