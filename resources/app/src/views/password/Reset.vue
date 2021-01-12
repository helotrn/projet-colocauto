<template>
  <div class="password-reset">
    <h1 class="password-reset__title">{{ $t('reset_password') }}</h1>

    <b-alert variant="danger" v-if="error" show>
      {{ $t('reset_response_error') }}
    </b-alert>

    <p>{{ $t('instructions') }}</p>

    <b-form class="password-reset__form" @submit.prevent="passwordReset" novalidate>
      <b-form-group :label="$t('email')">
        <b-form-input type="email" disabled :value="email" />
      </b-form-group>

      <forms-validated-input mode="lazy" name="new_password" :label="$t('new_password')"
        :rules="{ required: true, min: 8 }" type="password"
        :placeholder="$t('new_password')" description="Minimum 8 caractères"
        v-model="newPassword" />

      <forms-validated-input mode="lazy"
        name="new_password_repeat" :label="$t('new_password_repeat')"
        :rules="{ required: true, is: newPassword }" type="password"
        :placeholder="$t('new_password_repeat')"
        v-model="newPasswordRepeat" />

      <b-form-group>
        <b-button type="submit" :disabled="loading" variant="primary" block>
          {{ $t('submit') }}
        </b-button>
      </b-form-group>
    </b-form>
  </div>
</template>

<i18n>
fr:
  email: Courriel
  instructions: Entrez un nouveau mot de passe pour le compte associé à cette adresse.
  new_password: Nouveau mot de passe
  new_password_repeat: Nouveau mot de passe (confirmation)
  reset_response_error: Une erreur s'est produite. Vérifiez l'adresse utilisée et réessayez.
  reset_password: Réinitialisation du mot de passe
  submit: Mettre à jour
</i18n>

<script>
import FormsValidatedInput from '@/components/Forms/ValidatedInput.vue';

export default {
  name: 'PasswordReset',
  components: { FormsValidatedInput },
  data() {
    return {
      error: false,
      loading: false,
      newPassword: '',
      newPasswordRepeat: '',
    };
  },
  computed: {
    email() {
      if (!this.$route.query) {
        return null;
      }

      return this.$route.query.email;
    },
    token() {
      if (!this.$route.query) {
        return null;
      }

      return this.$route.query.token;
    },
  },
  methods: {
    async passwordReset() {
      this.loading = true;
      this.error = false;

      const {
        email,
        token,
        newPassword,
        newPasswordRepeat,
      } = this;

      try {
        await this.$store.dispatch('password/reset', {
          email,
          newPassword,
          newPasswordRepeat,
          token,
        });

        this.$store.commit('addNotification', {
          content: 'Vous pouvez maintenant vous connecter avec votre nouveau mot de passe.',
          title: 'Mot de passe mis à jour',
          variant: 'success',
          type: 'password',
        });

        this.$router.push('/login');
      } catch (e) {
        this.error = true;
      }

      this.loading = false;
    },
  },
};
</script>

<style lang="scss">
.password-reset {
  background-color: $white;
  padding: 53px $grid-gutter-width / 2 45px;
  width: 590px;
  max-width: 100%;
  margin: 0 auto;

  .password-reset__form {
    margin-top: 32px;
  }

  .password-reset__title {
    text-align: center;
    color: $black;
    font-size: 24px;
    margin-bottom: 20px;
  }
}
</style>
