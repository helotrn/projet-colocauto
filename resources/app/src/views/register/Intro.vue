<template>
  <div>
  <div class="register-intro box fixed-width">
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
  data() {
    return {
      currentPage: 1,
    };
  },
};
</script>
