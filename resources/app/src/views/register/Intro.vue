<template>
  <div>
  <div class="register-intro box">
    <register-form v-if="currentPage == 1" />
  </div>
  <login-hint />
  </div>
</template>

<script>
import Authenticated from "@/mixins/Authenticated";
import UserMixin from "@/mixins/UserMixin";

import RegisterForm from "@/components/Register/RegisterForm.vue";
import LoginHint from "@/components/Login/LoginHint.vue";

export default {
  name: "RegisterIntro",
  mixins: [Authenticated, UserMixin],
  components: {
    RegisterForm,
    LoginHint,
  },
  beforeRouteEnter(to, from, next) {
    next((vm) => {
      if (vm.isLoggedIn) {
        // Not register, go to "complete your profile"
        if (!vm.isRegistered) {
          if (vm.$route.path !== "/register/2") {
            return vm.$router.replace("/register/2");
          }
          return null;
        } else {
          return vm.skipToApp();
        }
      }

      if (vm.$route.path !== "/register/1") {
        return vm.$router.replace("/register/1");
      }

      return null;
    });
  },
  data() {
    return {
      currentPage: 1,
    };
  },
};
</script>
