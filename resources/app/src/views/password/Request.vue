<template>
  <div class="password-request">
    <h1 class="password-request__title">{{ $t("request_password") }}</h1>

    <b-alert variant="success" v-if="sent" show>
      {{ $t("request_response") }}
    </b-alert>

    <b-alert variant="danger" v-if="error" show>
      {{ $t("request_response_error") }}
    </b-alert>

    <p>{{ $t("instructions") }}</p>

    <b-form class="password-request__form" @submit.prevent="passwordRequest" novalidate>
      <b-form-group :label="$t('email')">
        <b-form-input type="email" required :placeholder="$t('email')" v-model="email" />
      </b-form-group>

      <b-form-group>
        <b-button type="submit" :disabled="loading" variant="primary" block>
          {{ $t("submit") }}
        </b-button>
      </b-form-group>
    </b-form>
  </div>
</template>

<script>
import locales from "@/locales";

export default {
  name: "PasswordRequest",
  data() {
    return {
      email: "",
      error: false,
      loading: false,
      sent: false,
    };
  },
  methods: {
    async passwordRequest() {
      this.sent = false;
      this.loading = true;
      this.error = false;

      try {
        await this.$store.dispatch("password/request", {
          email: this.email,
        });
        this.sent = true;
      } catch (e) {
        this.error = true;
      }

      this.loading = false;
    },
  },
  i18n: {
    messages: {
      fr: {
        ...locales.fr.views.password.request,
      },
    },
  },
};
</script>

<style lang="scss">
.password-request {
  background-color: $white;
  padding: 53px $grid-gutter-width / 2 45px;
  width: 590px;
  max-width: 100%;
  margin: 0 auto;

  .password-request__form {
    margin-top: 32px;
  }

  .password-request__title {
    text-align: center;
    color: $black;
    font-size: 24px;
    margin-bottom: 20px;
  }
}
</style>
