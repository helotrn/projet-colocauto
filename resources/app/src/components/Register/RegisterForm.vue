<template>
  <div class="register-form">
    <h2 class="text-center">{{ $t("register") }}</h2>

    <div class="register-form__google">
      <google-auth-button :label="$t('google')" />
    </div>

    <div class="form__separator">
      <span class="form__separator__text">{{ $t("or") }}</span>
    </div>

    <validation-observer ref="observer" v-slot="{ passes }">
      <b-form
        :novalidate="true"
        class="register-form__form"
        @submit.stop.prevent="passes(register)"
      >
        <forms-validated-input
          mode="eager"
          name="email"
          :label="$t('email')"
          :rules="{ required: true, email: true }"
          type="email"
          :placeholder="$t('email')"
          v-model="email"
        />

        <forms-validated-input
          mode="eager"
          name="password"
          :label="$t('password')"
          :rules="{ required: true, min: 8 }"
          type="password"
          :placeholder="$t('password')"
          :description="$t('password_length')"
          v-model="password"
        />

        <forms-validated-input
          mode="eager"
          name="password_repeat"
          :label="$t('password_repeat')"
          :rules="{ required: true, is: password }"
          type="password"
          :placeholder="$t('password_repeat')"
          v-model="passwordRepeat"
        />

        <b-button type="submit" :disabled="loading" variant="primary" block class="btn-register">
          {{ $t("register_submit") }}
        </b-button>
      </b-form>
    </validation-observer>
  </div>
</template>

<script>
import locales from "@/locales";

import FormsValidatedInput from "@/components/Forms/ValidatedInput.vue";
import GoogleAuthButton from "@/components/Misc/GoogleAuthButton.vue";

import { extractErrors } from "@/helpers";

export default {
  name: "registerBox",
  components: {
    FormsValidatedInput,
    GoogleAuthButton,
  },
  data() {
    return {
      password: "",
      passwordRepeat: "",
    };
  },
  computed: {
    loading() {
      return this.$store.state.loading;
    },
    email: {
      get() {
        return this.$store.state.register.email;
      },
      set(value) {
        return this.$store.commit("register/email", value);
      },
    },
  },
  methods: {
    async register() {
      this.$store.commit("loading", true);

      try {
        await this.$store.dispatch("register", {
          email: this.email,
          password: this.password,
        });

        await this.$store.dispatch("login", {
          email: this.email,
          password: this.password,
        });

        this.$router.replace("/register/2");
      } catch (e) {
        if (e.request) {
          switch (e.request.status) {
            case 422:
            default:
              this.$store.commit("addNotification", {
                content: extractErrors(e.response.data).join(", "),
                title: this.$i18n.t("register_error"),
                variant: "danger",
                type: "register",
              });
          }
        }
      }
    },
  },
  i18n: {
    messages: {
      fr: {
        ...locales.fr.components.register.registerform,
      },
      en: {
        ...locales.en.components.register.registerform,
      },
    },
  },
};
</script>

<style lang="scss">
.register-form {
  h2 {
    margin-bottom: 20px;
  }

  .btn-primary {
    margin-left: 0;

    &.btn-register {
      margin-top: 40px;
    }
  }

  &__google {
    text-align: center;
  }

  &__form {
    margin-top: 32px;
  }
}
</style>
