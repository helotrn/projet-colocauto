<template>
  <div class="register-form">
    <h2>{{ $t('register') }}</h2>
    <div class="register-form__google">
      <b-button :disabled="loading" variant="primary" class="btn-google">
        <div class="btn-google__icon">
          <svg-google />
        </div>
        {{ $t('google') }}
      </b-button>
    </div>

    <div class="register-form__separator">
      <span class="register-form__separator__text">{{ $t('or') }}</span>
    </div>

    <validation-observer ref="observer" v-slot="{ passes }">
      <b-form :novalidate="true" class="register-form__form"
        @submit.stop.prevent="passes(register)">
        <validation-provider
          :name="$t('email')"
          :rules="{ required: true, email: true }"
          v-slot="validationContext">
          <b-form-group :label="$t('email')">
            <b-form-input
              type="email"
              :placeholder="$t('email')"
              :state="getValidationState(validationContext)"
              v-model="email" />
            <b-form-invalid-feedback>
              {{ validationContext.errors[0] }}
            </b-form-invalid-feedback>
          </b-form-group>
        </validation-provider>

        <validation-provider
          :name="$t('password')"
          :rules="{ required: true, min: 8 }"
          v-slot="validationContext">
          <b-form-group :label="$t('password')">
            <b-form-input
              type="password"
              :placeholder="$t('password')"
              :state="getValidationState(validationContext)"
              v-model="password"
            />
            <b-form-invalid-feedback>
              {{ validationContext.errors[0] }}
            </b-form-invalid-feedback>
          </b-form-group>
        </validation-provider>

        <validation-provider
          :name="$t('password-repeat')"
          :rules="{ required: true, is: password }"
          v-slot="validationContext">
          <b-form-group :label="$t('password-repeat')">
            <b-form-input
              type="password"
              :placeholder="$t('password-repeat')"
              :state="getValidationState(validationContext)"
              v-model="passwordRepeat"
            />
          </b-form-group>
        </validation-provider>

        <b-button type="submit" :disabled="loading" variant="primary" block>
          {{ $t('register-submit') }}
        </b-button>
      </b-form>
    </validation-observer>
  </div>
</template>

<i18n>
fr:
  'register': Inscription
  'google': Inscription Google
  'or': OU
  'email': Courriel
  'password': Mot de passe
  'password-repeat': Mot de passe (confirmation)
  'register-submit': S'inscrire
en:
  'register': Register
  'google': Sign up with Google
  'or': OR
  'email': Email
  'password': Password
  'password-repeat': Password (confirmation)
  'register-submit': Register
</i18n>

<script>
import helpers from '@/helpers';
import Google from '@/assets/svg/google.svg';

const { extractErrors } = helpers;

export default {
  name: 'registerBox',
  components: {
    'svg-google': Google,
  },
  data() {
    return {
      password: '',
      passwordRepeat: '',
    };
  },
  computed: {
    loading() {
      return this.$store.state.register.loading;
    },
    email: {
      get() {
        return this.$store.state.register.email;
      },
      set(value) {
        return this.$store.commit('register/email', value);
      },
    },
  },
  methods: {
    getValidationState({ dirty, validated, valid = null }) {
      return dirty || validated ? valid : null;
    },
    paginationLinks(pageNum) {
      return pageNum > 1 ? `/register/${pageNum}` : '/register';
    },
    async register() {
      this.$store.commit('register/loading', true);

      try {
        await this.$store.dispatch('register', {
          email: this.email,
          password: this.password,
        });

        await this.$store.dispatch('login', {
          email: this.email,
          password: this.password,
        });

        this.$store.commit('register/loading', false);

        this.$router.replace('/register/2');
      } catch (e) {
        if (e.request) {
          switch (e.request.status) {
            case 422:
            default:
              this.$store.commit('addNotification', {
                content: extractErrors(e.response.data).join(', '),
                title: "Erreur d'inscription",
                variant: 'danger',
                type: 'register',
              });
          }
        }
      }

      this.$store.commit('register/loading', false);
    },
  },
};
</script>

<style lang="scss">
.register-form__google {
  text-align: center;
}

.register-form__separator {
  text-align: center;
  margin: 24px 0;
  border-bottom: 1px solid $black;
}

.register-form__separator__text {
  position: relative;
  top: 11px;
  padding: 0 20px;
  background: $white;
}
</style>
