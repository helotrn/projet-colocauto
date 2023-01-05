<template>
  <layout-page name="wallet">
    <router-view />
  </layout-page>
</template>

<script>
import Authenticated from "@/mixins/Authenticated";
import UserMixin from "@/mixins/UserMixin";

const routeGuard = (to, from, next) => {
  if (to.name === "wallet") {
    next("/wallet/expenses");
  } else {
    next();
  }
};

export default {
  name: "Wallet",
  mixins: [Authenticated, UserMixin],
  beforeRouteEnter: routeGuard,
  beforeRouteUpdate: routeGuard,
  beforeRouteLeave(to, from, next) {
    // Set the root store as not loaded to force a reload of the user
    this.$store.commit("loaded", false);
    next();
  },
  computed: {
    pageTitle() {
      return this.$i18n.t(`wallet.${this.$route.meta.title}`);
    },
  },
};
</script>

<style lang="scss">
.wallet.page {
  .page__content {
    padding: 45px 30px;
  }
}
</style>
