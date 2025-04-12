<template>
  <div class="login-box box">
    <h1 class="login-box__title">{{ $t("login") }}</h1>

    <b-form class="login-box__form" @submit.prevent="login" novalidate>
      <b-form-group :label="$t('email')">
        <b-form-input type="email" required :placeholder="$t('email')" v-model="email" />
      </b-form-group>

      <b-form-group :label="$t('password')" class="login-box__pw-group">
        <b-form-input
          :type="passwordIsVisible ? 'text' : 'password'"
          required
          :placeholder="$t('password')"
          v-model="password"
          id="password"
        />
        <button
          type="button"
          class="login-box__show-pw"
          :aria-pressed="passwordIsVisible ? 'true' : 'false'"
          aria-controls="password"
          @click="hideShowPassword"
          :title="passwordIsVisible ? $t('hide_password') : $t('show_password')"
        >
          <eye-slash-icon v-if="passwordIsVisible" aria-hidden="true" focusable="false" />
          <eye-icon v-else aria-hidden="true" focusable="false" />
          <span class="sr-only">{{ passwordIsVisible ? $t("hide_password") : $t("show_password") }}</span>
        </button>
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
    <div class="sr-only" aria-live="assertive">
      <template v-if="announcePasswordVisibility">
        {{ passwordIsVisible ? $t("password_shown") : $t("password_hidden") }}
      </template>
    </div>

    <div class="text-right">
      <router-link to="/password/request">
        <small>{{ $t("forgot_password") }}</small>
      </router-link>
    </div>
  </div>
</template>

<script>
import locales from "@/locales";
import eyeIcon from "@/assets/icons/eye.svg"
import eyeSlashIcon from "@/assets/icons/eye-slash.svg"

export default {
  name: "LoginBox",
  data() {
    return {
      password: "",
      passwordIsVisible: false,
      announcePasswordVisibility: false,
    };
  },
  components: {
    eyeIcon,
    eyeSlashIcon,
  },
  computed: {
    loading() {
      return this.$store.state.loading;
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
    hideShowPassword() {
      this.announcePasswordVisibility = true;
      this.passwordIsVisible = !this.passwordIsVisible;
    },
    async login() {
      try {
        this.announcePasswordVisibility = false;
        this.passwordIsVisible = false;
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

  &__form {
    margin-top: 32px;
  }

  &__title {
    text-align: center;
    color: $black;
    font-size: 24px;
    margin-bottom: 20px;
  }

  &__pw-group {
    position: relative;
  }

  &__show-pw {
    background: transparent;
    border: none;
    position: absolute;
    top: 0;
    right: 0;
    padding: 0.375rem 0.75rem;

    &:focus {
      outline: 0;
      box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075), 0 0 0 0.2rem rgba(36, 90, 234, 0.25);
    }
  }

  .google-login {
    text-align: center;
  }
}
</style>
