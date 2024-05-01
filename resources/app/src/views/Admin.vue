<template>
  <layout-page name="admin" :fluid="isFluid">
    <div class="position-relative">
      <div class="toggles" :class="{ 'pl-3': showSidebar }">
        <b-button variant="outline-primary" size="sm" :pressed.sync="isFluid"
          ><b-icon-arrows-fullscreen v-if="!isFluid"></b-icon-arrows-fullscreen
          ><b-icon-fullscreen-exit v-else></b-icon-fullscreen-exit></b-button
        >&nbsp;
        <b-button variant="outline-primary" size="sm" :pressed.sync="showSidebar"
          ><b-icon-layout-sidebar
        /></b-button>
      </div>
      <b-row>
        <b-col class="admin__sidebar" v-if="showSidebar">
          <b-nav vertical>
            <admin-sidebar :is-global-admin="isGlobalAdmin" :is-community-admin="isCommunityAdmin" />
          </b-nav>
        </b-col>

        <b-col class="admin__view">
          <router-view />
        </b-col>
      </b-row>
    </div>
  </layout-page>
</template>

<script>
import AdminSidebar from "@/components/Admin/Sidebar.vue";

import Authenticated from "@/mixins/Authenticated";
import UserMixin from "@/mixins/UserMixin";

export default {
  name: "Admin",
  mixins: [Authenticated, UserMixin],
  components: { AdminSidebar },
  data: () => ({
    isFluid: false,
    showSidebar: true,
  }),
  mounted() {
    if (!this.isAdmin) {
      this.$router.replace("/app");
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
  .toggles {
    position: absolute;
    top: -2rem;
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
