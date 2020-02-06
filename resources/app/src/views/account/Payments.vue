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
        <payments />
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

import Payments from '@/components/Account/Payments.vue';
import Sidebar from '@/components/Account/Sidebar.vue';

export default {
  name: 'Payments',
  components: {
    Payments,
    Sidebar,
  },
  beforeMount() {
    if (!this.loggedIn) {
      this.skipToLogin('app');
    }
  },
};
</script>
