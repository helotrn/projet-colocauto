<template>
  <div class="profile-account" v-if="item">
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
  </div>
  <layout-loading v-else />
</template>

<script>
import UserEmailForm from '@/components/User/EmailForm.vue';
import UserPasswordForm from '@/components/User/PasswordForm.vue';

import FormMixin from '@/mixins/FormMixin';

export default {
  name: 'ProfileAccount',
  mixins: [FormMixin],
  components: {
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
  },
};
</script>

<style lang="scss">
.profile-account {
  margin-bottom: 3em;
}
</style>
