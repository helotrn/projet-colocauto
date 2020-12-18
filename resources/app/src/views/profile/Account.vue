<template>
  <div class="profile-account" v-if="item && routeDataLoaded">
    <div class="form__section">
      <h2>Changer mon mot de passe</h2>

      <user-password-form ref="passwordForm"
        :user="item" :loading="loading" @updated="resetPasswordForm" />
    </div>

    <div class="form__section">
      <h2>Changer mon courriel</h2>

      <user-email-form ref="emailForm"
        :user="item" :loading="loading" @updated="resetEmailForm" />
    </div>

    <div class="form__section">
      <h2>Conditions d'utilisation</h2>

      <forms-validated-input type="checkbox" name="accept_conditions"
        :label="$t('users.fields.accept_conditions') | capitalize"
        :value="item.accept_conditions"
        @input="updateAcceptConditions" :disabled="item.accept_conditions" />
    </div>
  </div>
  <layout-loading v-else />
</template>

<script>
import FormsValidatedInput from '@/components/Forms/ValidatedInput.vue';
import UserEmailForm from '@/components/User/EmailForm.vue';
import UserPasswordForm from '@/components/User/PasswordForm.vue';

import DataRouteGuards from '@/mixins/DataRouteGuards';
import FormMixin from '@/mixins/FormMixin';

export default {
  name: 'ProfileAccount',
  mixins: [DataRouteGuards, FormMixin],
  components: {
    FormsValidatedInput,
    UserEmailForm,
    UserPasswordForm,
  },
  props: {
    id: {
      required: false,
      default: 'me',
    },
  },
  methods: {
    resetEmailForm() {
      this.$refs.emailForm.reset();
    },
    resetPasswordForm() {
      this.$refs.passwordForm.reset();
    },
    async updateAcceptConditions(value) {
      await this.$store.dispatch('users/update', {
        id: this.item.id,
        data: {
          id: this.item.id,
          accept_conditions: value,
        },
        params: {
          fields: 'id,name,accept_conditions',
        },
      });
    },
  },
};
</script>

<style lang="scss">
.profile-account {
  margin-bottom: 3em;
}
</style>
