<template>
  <div>
  <div class="register-intro box">
    <b-pagination-nav
      v-bind:value="currentPage"
      :number-of-pages="3"
      pills
      align="center"
      use-router
      :hide-goto-end-buttons="true"
      :disabled="true"
    >
      <template v-slot:page="{ page, active }">
        <span v-if="page < currentPage" class="checked">
          <b-icon icon="check" font-scale="2" />
        </span>
        <span v-else>{{ page }}</span>
      </template>
    </b-pagination-nav>

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
