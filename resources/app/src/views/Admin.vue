<template>
  <layout-page name="admin">
    <b-row>
      <b-col class="admin__menu">
        <b-nav vertical>
          <admin-menu />
        </b-nav>
      </b-col>

      <b-col class="admin__view">
        <router-view />
      </b-col>
    </b-row>
  </layout-page>
</template>

<script>
import AdminMenu from '@/components/Admin/Menu.vue';

export default {
  name: 'Admin',
  components: { AdminMenu },
};
</script>

<style lang="scss">
.admin {
  .page__content {
    .admin__menu.col {
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
