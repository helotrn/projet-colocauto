<template>
  <div class="email-form">
    <validation-observer ref="observer" v-slot="{ passes }">
      <b-form :novalidate="true" class="email-form__form"
        @submit.stop.prevent="passes(updateEmail)">
        <forms-validated-input mode="lazy"
          name="current_password" :label="$t('current_password')"
          :rules="{ required: true }" type="password"
          :placeholder="$t('current_password')"
          v-model="currentPassword" />

        <forms-validated-input mode="lazy" name="new_email" :label="$t('new_email')"
          :rules="{ required: true, email: true }" type="email"
          :placeholder="$t('new_email')"
          v-model="newEmail" />

        <forms-validated-input mode="lazy"
          name="new_email_repeat" :label="$t('new_email_repeat')"
          :rules="{ required: true, is: newEmail }" type="email"
          :placeholder="$t('new_email_repeat')"
          v-model="newEmailRepeat" />

        <b-button type="submit" :disabled="loading" variant="primary" v-if="!!user.id">
          {{ $t('submit') }}
        </b-button>
      </b-form>
    </validation-observer>
  </div>
</template>

<i18n>
fr:
  current_password: Mot de passe actuel
  new_email: Nouveau courriel
  new_email_repeat: Nouveau courriel (confirmation)
  submit: Mettre à jour
</i18n>

<script>
import FormsValidatedInput from '@/components/Forms/ValidatedInput.vue';

import { extractErrors } from '@/helpers';

export default {
  name: 'UserEmailForm',
  components: {
    FormsValidatedInput,
  },
  props: {
    isAdmin: {
      type: Boolean,
      required: false,
      default: false,
    },
    loading: {
      type: Boolean,
      required: false,
      default: false,
    },
    user: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      currentPassword: '',
      newEmail: '',
      newEmailRepeat: '',
    };
  },
  methods: {
    async updateEmail() {
      const { currentPassword, newEmail, user: { id: userId } } = this;

      try {
        await this.$store.dispatch('users/updateEmail', {
          currentPassword,
          newEmail,
          userId,
        });

        this.$store.commit('addNotification', {
          content: 'Vous pouvez maintenant vous connecter avec votre nouveau courriel.',
          title: 'Courriel mis-à-jour',
          variant: 'success',
          type: 'email',
        });

        this.$emit('updated');
      } catch (e) {
        if (e.request) {
          switch (e.request.status) {
            case 422:
              this.$store.commit('addNotification', {
                content: extractErrors(e.response.data).join(', '),
                title: 'Erreur de validation',
                variant: 'danger',
                type: 'email',
              });
              break;
            default:
              throw e;
          }
        } else {
          throw e;
        }
      }
    },
    reset() {
      this.currentPassword = '';
      this.newEmail = '';
      this.newEmailRepeat = '';
    },
  },
};
</script>

<style lang="scss">
</style>
