<template>
  <div class="profile-account" v-if="item && routeDataLoaded">
    <div class="form__section">
      <h2>Informations générales</h2>

      <profile-form
        :loading="loading"
        :user="item"
        :form="form"
        @reset="reset"
        :changed="changed"
        show-reset
        @submit="submit"
        v-if="item"
      >
        <template>
          <b-row v-if="hasAddressChanged">
            <b-col>
              <b-alert variant="danger" show class="address-change-warning">
                Si votre changement d'adresse entraine un changement de quartier, vous devrez
                soumettre une nouvelle preuve de résidence.
              </b-alert>
            </b-col>
          </b-row>
        </template>
      </profile-form>
    </div>

    <div class="form__section">
      <h2>Changer mon mot de passe</h2>

      <user-password-form
        ref="passwordForm"
        :user="item"
        :loading="loading"
        @updated="resetPasswordFormAndShowModal"
      />

      <b-modal
        size="md"
        header-bg-variant="success"
        title-class="font-weight-bold"
        ok-only
        no-close-on-backdrop
        no-close-on-esc
        hide-header-close
        :title="$t('views.profile.account.password_change_modal.title')"
        id="password-change-modal"
      >
        <div v-html="$t('views.profile.account.password_change_modal.content')" />
      </b-modal>
    </div>

    <div class="form__section">
      <h2>Changer mon courriel</h2>

      <p>{{ $t("views.profile.account.current_email") }}&nbsp;: {{ item.email }}.</p>

      <user-email-form
        ref="emailForm"
        :user="item"
        :loading="loading"
        @updated="resetEmailFormAndShowModal"
      />

      <b-modal
        size="md"
        header-bg-variant="success"
        title-class="font-weight-bold"
        ok-only
        no-close-on-backdrop
        no-close-on-esc
        hide-header-close
        :title="$t('views.profile.account.email_change_modal.title')"
        id="email-change-modal"
      >
        <div v-html="$t('views.profile.account.email_change_modal.content')" />
      </b-modal>
    </div>

    <div class="form__section">
      <h2>Conditions d'utilisation</h2>

      <conditions-updated-toast />

      <forms-validated-input
        type="checkbox"
        name="accept_conditions"
        :label="$t('users.fields.accept_conditions') | capitalize"
        :value="item.accept_conditions"
        @input="updateAcceptConditions"
        :disabled="item.accept_conditions"
      />

      <forms-validated-input
        type="checkbox"
        name="gdpr"
        :label="$t('users.fields.gdpr') | capitalize"
        :value="item.gdpr"
        @input="updateGDPR"
        :disabled="item.gdpr"
      />

      <forms-validated-input
        type="checkbox"
        name="newsletter"
        :label="$t('users.fields.newsletter') | capitalize"
        :value="item.newsletter"
        @input="updateNewsletter"
      />
    </div>

  </div>
  <layout-loading v-else />
</template>

<script>
import ProfileForm from "@/components/Profile/ProfileForm.vue";
import FormsValidatedInput from "@/components/Forms/ValidatedInput.vue";
import UserEmailForm from "@/components/User/EmailForm.vue";
import UserPasswordForm from "@/components/User/PasswordForm.vue";
import ConditionsUpdatedToast from "@/views/ConditionsUpdatedToast.vue";

import DataRouteGuards from "@/mixins/DataRouteGuards";
import FormMixin from "@/mixins/FormMixin";

export default {
  name: "ProfileAccount",
  mixins: [DataRouteGuards, FormMixin],
  components: {
    ProfileForm,
    FormsValidatedInput,
    UserEmailForm,
    UserPasswordForm,
    ConditionsUpdatedToast,
  },
  props: {
    id: {
      required: false,
      default: "me",
    },
  },
  computed: {
    hasAddressChanged() {
      return this.item.address !== this.initialItem.address;
    },
  },
  methods: {
    resetEmailFormAndShowModal() {
      this.$refs.emailForm.reset();
      this.$bvModal.show("email-change-modal");
    },
    resetPasswordFormAndShowModal() {
      this.$refs.passwordForm.reset();
      this.$bvModal.show("password-change-modal");
    },
    async updateAcceptConditions(value) {
      await this.$store.dispatch("users/update", {
        id: this.item.id,
        data: {
          id: this.item.id,
          accept_conditions: value,
        },
        params: this.$route.meta.params,
      });
    },
    async updateGDPR(value) {
      await this.$store.dispatch("users/update", {
        id: this.item.id,
        data: {
          id: this.item.id,
          gdpr: value,
        },
        params: this.$route.meta.params,
      });
    },
    async updateNewsletter(value) {
      await this.$store.dispatch("users/update", {
        id: this.item.id,
        data: {
          id: this.item.id,
          newsletter: value,
        },
        params: this.$route.meta.params,
      });
    },
  },
};
</script>

<style lang="scss">
.profile-account {
  margin-bottom: 3em;
  .address-change-warning {
    margin-top: 20px;
  }
}
</style>
