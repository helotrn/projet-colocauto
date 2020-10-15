<template>
  <layout-page name="admin">
    <b-row>
      <b-col class="admin__sidebar">
        <b-nav vertical>
          <admin-sidebar :is-global-admin="isGlobalAdmin" />
        </b-nav>
      </b-col>

      <b-col class="admin__view">
        <router-view />
      </b-col>
    </b-row>
  </layout-page>
</template>

<script>
import AdminSidebar from '@/components/Admin/Sidebar.vue';

import Authenticated from '@/mixins/Authenticated';
import UserMixin from '@/mixins/UserMixin';

export default {
  name: 'Admin',
  mixins: [Authenticated, UserMixin],
  components: { AdminSidebar },
  mounted() {
    if (!this.isAdmin) {
      this.$router.replace('/app');
    }
  },
};
</script>

<style lang="scss">
.admin {
  &__buttons {
    text-align: right;
    line-height: 48px;
    margin-bottom: 10px;
  }

  &__filters {
    text-align: right;
  }

  &__selection {
    margin-bottom: 10px;
    min-height: 19px;
    text-align: left;
  }

  .page__content {
    padding-top: 45px;
    padding-bottom: 45px;

    .admin__sidebar.col {
      flex: 0 1 12vw;
    }

    .admin__view {
      h1 {
        margin-bottom: 10px;
      }
    }
  }
}
</style>
