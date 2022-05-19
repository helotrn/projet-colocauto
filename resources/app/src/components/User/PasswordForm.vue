<template>
  <div class="password-form">
    <validation-observer ref="observer" v-slot="{ passes }">
      <b-form
        :novalidate="true"
        class="password-form__form"
        @submit.stop.prevent="passes(updatePassword)"
      >
        <forms-validated-input
          v-if="!isAdmin"
          mode="lazy"
          name="current_password"
          :label="$t('current_password')"
          :rules="{ required: !isAdmin || !!newPassword }"
          type="password"
          :placeholder="$t('current_password')"
          v-model="currentPassword"
        />

        <forms-validated-input
          mode="lazy"
          name="new_password"
          :label="$t('new_password')"
          :rules="{ required: !user.id || !isAdmin, min: 8 }"
          type="password"
          :placeholder="$t('new_password')"
          description="Minimum 8 caractères"
          v-model="newPassword"
        />

        <forms-validated-input
          mode="lazy"
          name="new_password_repeat"
          :label="$t('new_password_repeat')"
          :rules="{ required: !user.id || !isAdmin, is: newPassword }"
          type="password"
          :placeholder="$t('new_password_repeat')"
          v-model="newPasswordRepeat"
        />

        <b-button type="submit" :disabled="loading" variant="primary" v-if="!!user.id">
          {{ $t("submit") }}
        </b-button>
      </b-form>
    </validation-observer>
  </div>
</template>

<i18n>
fr:
  current_password: Mot de passe actuel
  new_password: Nouveau mot de passe
  new_password_repeat: Nouveau mot de passe (confirmation)
  submit: Mettre à jour
</i18n>

<script>
import FormsValidatedInput from "@/components/Forms/ValidatedInput.vue";

export default {
  name: "UserPasswordForm",
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
      currentPassword: "",
      newPassword: "",
      newPasswordRepeat: "",
    };
  },
  methods: {
    async updatePassword() {
      const {
        currentPassword,
        newPassword,
        user: { id: userId },
      } = this;

      const response = await this.$store.dispatch("users/updatePassword", {
        currentPassword,
        newPassword,
        userId,
      });

      // if response is received, the password has been succesfully updated, and we show the success message.
      if (response) this.$emit("updated");
    },
    reset() {
      this.currentPassword = "";
      this.newPassword = "";
      this.newPasswordRepeat = "";
      this.$refs.observer.reset();
    },
  },
  watch: {
    newPassword(val) {
      if (!this.user.id) {
        this.user.password = val;
      }
    },
  },
};
</script>

<style lang="scss"></style>
