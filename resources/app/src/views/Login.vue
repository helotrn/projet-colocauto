<template>
  <layout-page name="login" wide bg-color="green" bg-image padded>
    <login-box />
    <register-hint />
  </layout-page>
</template>

<script>
import Authenticated from "@/mixins/Authenticated";
import UserMixin from "@/mixins/UserMixin";

import LoginBox from "@/components/Login/Box.vue";
import RegisterHint from "@/components/Login/RegisterHint.vue";

export default {
  name: "Login",
  mixins: [Authenticated, UserMixin],
  beforeRouteEnter(to, from, next) {
    next((vm) => {
      if (vm.isLoggedIn) {
        vm.skipToApp();
      }

      if (to.query.token) {
        vm.$store.commit("token", to.query.token);
        vm.$store.dispatch("loadUser").then(() => vm.$router.replace("/app"));
      }
    });
  },
  components: { LoginBox, RegisterHint },
};
</script>

<style lang="scss"></style>
