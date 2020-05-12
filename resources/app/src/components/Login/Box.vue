<template>
  <div class="login-box">
    <h1 class="login-box__title">{{ $t('login') }}</h1>

    <div class="google-login">
      <b-button href="/auth/google" variant="primary" class="btn-google">
        <div class="btn-google__icon">
          <svg-google />
        </div>
        {{ $t('google') }}
      </b-button>
    </div>

    <div class="form__separator">
      <span class="form__separator__text">{{ $t('or') }}</span>
    </div>

    <b-form class="login-box__form" @submit.prevent="login" novalidate>
      <b-form-group :label="$t('email')">
        <b-form-input
          type="email"
          required
          :placeholder="$t('email')"
          v-model="email"
        />
      </b-form-group>

      <b-form-group :label="$t('password')">
        <b-form-input
          type="password"
          required
          :placeholder="$t('password')"
          v-model="password"
        />
      </b-form-group>

      <b-form-group>
        <b-form-checkbox inline v-model="rememberMe">
          {{ $t('remember-me') }}
        </b-form-checkbox>
      </b-form-group>

      <b-form-group>
        <b-button type="submit" :disabled="loading" variant="primary" block>
          {{ $t('login-submit') }}
        </b-button>
      </b-form-group>
    </b-form>
  </div>
</template>

<i18n>
fr:
  'email': Courriel
  'google': Se connecter avec Google
  'login': Connexion
  'login-submit': Se connecter
  'or': OU
  'password': Mot de passe
  'remember-me': Se souvenir de moi
en:
  'email': Email
  'google': Sign in with Google
  'login': Login
  'login-submit': Login
  'or': OR
  'password': Password
  'remember-me': Remember me
</i18n>

<script>
import Google from '@/assets/svg/google.svg';

export default {
  name: 'LoginBox',
  components: {
    'svg-google': Google,
  },
  data() {
    return {
      password: '',
    };
  },
  computed: {
    loading() {
      return this.$store.state.login.loading;
    },
    email: {
      get() {
        return this.$store.state.login.email;
      },
      set(value) {
        return this.$store.commit('login/email', value);
      },
    },
    rememberMe: {
      get() {
        return this.$store.state.login.rememberMe;
      },
      set(value) {
        return this.$store.commit('login/rememberMe', value);
      },
    },
  },
  methods: {
    async login() {
      this.$store.commit('login/loading', true);

      try {
        await this.$store.dispatch('login', {
          email: this.email,
          password: this.password,
        });

        this.$store.commit('login/loading', false);

        if (this.$route.query.r) {
          this.$router.replace(this.$route.query.r);
        } else {
          this.$router.replace('/app');
        }
      } catch (e) {
        if (e.request) {
          switch (e.request.status) {
            case 401:
              this.$store.commit('addNotification', {
                content: "Nom d'utilisateur ou mot de passe invalide.",
                title: 'Erreur de connexion.',
                variant: 'danger',
                type: 'login',
              });
              break;
            default:
              this.$store.commit('addNotification', {
                content: `Erreur de communication avec le serveur. (${e.message})`,
                title: 'Erreur de connexion.',
                variant: 'danger',
                type: 'login',
              });
              break;
          }
        }
      }

      this.$store.commit('login/loading', false);
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
