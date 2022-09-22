<template>
  <div class="login-box">
    <h1 class="login-box__title">{{ $t("login") }}</h1>

    <div class="google-login">
      <google-auth-button :label="$t('google')" />
    </div>

    <div class="form__separator">
      <span class="form__separator__text">{{ $t("or") }}</span>
    </div>

    <b-form class="login-box__form" @submit.prevent="login" novalidate>
      <b-form-group :label="$t('email')">
        <b-form-input type="email" required :placeholder="$t('email')" v-model="email" />
      </b-form-group>

      <b-form-group :label="$t('password')">
        <b-form-input type="password" required :placeholder="$t('password')" v-model="password" />
      </b-form-group>

      <b-form-group>
        <b-form-checkbox inline v-model="rememberMe">
          {{ $t("remember_me") }}
        </b-form-checkbox>
      </b-form-group>

      <b-form-group class="mb-1">
        <b-button type="submit" :disabled="loading" variant="primary" block>
          {{ $t("login_submit") }}
        </b-button>
      </b-form-group>
    </b-form>

    <div class="text-right">
      <router-link to="/password/request">
        <small>{{ $t("forgot_password") }}</small>
      </router-link>
    </div>
  </div>
</template>

<script>
import locales from "@/locales";
import GoogleAuthButton from "@/components/Misc/GoogleAuthButton.vue";

export default {
  name: "LoginBox",
  components: {
    GoogleAuthButton,
  },
  data() {
    return {
      password: "",
    };
  },
  computed: {
    loading() {
      return this.$store.state.loading;
    },
    authUrl() {
      return `${process.env.VUE_APP_BACKEND_URL}/auth/google`;
    },
    email: {
      get() {
        return this.$store.state.login.email;
      },
      set(value) {
        return this.$store.commit("login/email", value);
      },
    },
    rememberMe: {
      get() {
        return this.$store.state.login.rememberMe;
      },
      set(value) {
        return this.$store.commit("login/rememberMe", value);
      },
    },
  },
  methods: {
    async login() {
      try {
        await this.$store.dispatch("login", {
          email: this.email,
          password: this.password,
        });

        if (this.$route.query.r) {
          this.$router.replace(this.$route.query.r);
        } else {
          this.$router.replace("/app");
        }
      } catch (e) {
        if (e.request) {
          switch (e.request.status) {
            case 401:
              this.$store.commit("addNotification", {
                content: "Nom d'utilisateur ou mot de passe invalide.",
                title: "Erreur de connexion.",
                variant: "danger",
                type: "login",
              });
              break;
            default:
              this.$store.commit("addNotification", {
                content: `Erreur de communication avec le serveur. (${e.message})`,
                title: "Erreur de connexion.",
                variant: "danger",
                type: "login",
              });
              break;
          }
        }
      }
    },
  },
  i18n: {
    messages: {
      fr: {
        ...locales.fr.components.login.box,
      },
      en: {
        ...locales.en.components.login.box,
      },
    },
  },
};
</script>

<style lang="scss">
.login-box {
  background-color: $white;
  padding: 53px $grid-gutter-width / 2 45px;
  width: 590px;
  max-width: 100%;
  margin: 0 auto;

  .login-box__form {
    margin-top: 32px;
  }

  .login-box__title {
    text-align: center;
    color: $black;
    font-size: 24px;
    margin-bottom: 20px;
  }

  .google-login {
    text-align: center;
  }
}
</style>
