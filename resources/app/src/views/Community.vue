<template>
  <layout-page name="community" padded :loading="!routeDataLoaded">
    <b-row>
      <b-col md="4" lg="3">
        <community-sidebar v-if="user.main_community" />
      </b-col>

      <b-col md="8" lg="9">
        <router-view />
      </b-col>
    </b-row>
  </layout-page>
</template>


<script>
import CommunitySidebar from "@/components/Community/Sidebar.vue";

import Authenticated from "@/mixins/Authenticated";
import UserMixin from "@/mixins/UserMixin";
import DataRouteGuards from "@/mixins/DataRouteGuards";

export default {
  name: "Community",
  mixins: [Authenticated, UserMixin, DataRouteGuards],
  components: {
    CommunitySidebar,
  },
};
</script>
