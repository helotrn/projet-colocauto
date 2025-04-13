<template>
  <layout-page name="profile" padded>
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
import ProfileSidebar from "@/components/Profile/Sidebar.vue";

import Authenticated from "@/mixins/Authenticated";
import UserMixin from "@/mixins/UserMixin";

const routeGuard = (to, from, next) => {
  if (to.name === "profile") {
    next("/profile/colocauto");
  } else {
    next();
  }
};

export default {
  name: "Account",
  mixins: [Authenticated, UserMixin],
  components: {
    ProfileSidebar,
  },
  beforeRouteEnter: routeGuard,
  beforeRouteUpdate: routeGuard,
  beforeRouteLeave(to, from, next) {
    // Set the root store as not loaded to force a reload of the user
    this.$store.commit("loaded", false);
    next();
  },
  computed: {
    pageTitle() {
      return this.$i18n.t(`profile.${this.$route.meta.title}`);
    },
  },
};
</script>
