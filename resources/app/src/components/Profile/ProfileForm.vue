<template>
  <div class="profile-form">
    <validation-observer ref="profileFormObserver" v-slot="{ passes }">
      <b-form novalidate class="profile-form__form form" @submit.stop.prevent="passes(submit)">
        <b-row>
          <b-col lg="4">
            <forms-validated-input
              type="image"
              name="avatar"
              :rules="{ required: true }"
              :description="$t('descriptions.avatar')"
              label="📷 Ajouter une photo de profil"
              v-model="user.avatar"
            />
          </b-col>
          <b-col>
            <b-row>
              <b-col lg="6">
                <forms-validated-input
                  name="name"
                  :label="$t('fields.name') | capitalize"
                  :rules="form.general.name.rules"
                  type="text"
                  @keypress.native="onlyChars"
                  :placeholder="placeholderOrLabel('name') | capitalize"
                  v-model="user.name"
                />
              </b-col>

              <b-col lg="6">
                <forms-validated-input
                  name="last_name"
                  :label="$t('fields.last_name') | capitalize"
                  :rules="form.general.last_name.rules"
                  type="text"
                  :placeholder="placeholderOrLabel('last_name') | capitalize"
                  v-model="user.last_name"
                />
              </b-col>
            </b-row>

            <b-row>
              <b-col>
                <forms-validated-input
                  name="description"
                  :description="$t('descriptions.description')"
                  label="Pour briser la glace"
                  type="textarea"
                  :placeholder="placeholderOrLabel('description') | capitalize"
                  v-model="user.description"
                />
              </b-col>
            </b-row>
          </b-col>
        </b-row>

        <hr />

        <b-alert variant="warning" show>
          Les données que vous entrez sont partagées avec l'équipe de LocoMotion et Desjardins
          Assurances. On partage également votre courriel et numéro de téléphone avec votre comité
          de voisinage et les personnes avec qui vous faites des prêts/emprunts. Consultez notre
          <a href="/privacy" target="_blank">politique de confidentialité</a>.
        </b-alert>

        <b-row>
          <b-col>
            <forms-validated-input
              name="phone"
              :label="$t('fields.phone') | capitalize"
              :rules="form.general.phone.rules"
              type="text"
              mask="(###) ###-####"
              :placeholder="placeholderOrLabel('phone') | capitalize"
              v-model="user.phone"
            />
          </b-col>
        </b-row>

        <b-alert :variant="age < 18 ? 'danger' : 'warning'" :show="age < 21">
          L'âge minimal pour utiliser LocoMotion est de 18 ans pour les vélos et les remorques; 21
          ans pour les autos.
        </b-alert>

        <b-row>
          <b-col>
            <forms-validated-input
              name="date_of_birth"
              :label="$t('fields.date_of_birth') | capitalize"
              :rules="dateOfBirthRules"
              type="date"
              initial-view="year"
              :placeholder="placeholderOrLabel('date_of_birth') | capitalize"
              :open-date="openDate"
              :disabled-dates="datesInTheFuture"
              v-model="user.date_of_birth"
            />
          </b-col>
        </b-row>

        <b-row>
          <b-col>
            <forms-validated-input
              name="address"
              :label="$t('fields.address') | capitalize"
              :rules="form.general.address.rules"
              type="text"
              :placeholder="placeholderOrLabel('address') | capitalize"
              v-model="user.address"
            />
          </b-col>
        </b-row>

        <b-row>
          <b-col>
            <forms-validated-input
              name="postal_code"
              :label="$t('fields.postal_code') | capitalize"
              :rules="form.general.postal_code.rules"
              type="text"
              mask="A#A #A#"
              :placeholder="placeholderOrLabel('postal_code') | capitalize"
              v-model="user.postal_code"
            />
          </b-col>
        </b-row>

        <slot />

        <div class="form__buttons" v-if="!hideButtons">
          <b-button-group v-if="showReset">
            <b-button variant="success" type="submit" :disabled="!changed || loading">
              {{ $t("enregistrer") | capitalize }}
            </b-button>
            <b-button type="reset" :disabled="!changed || loading" @click="$emit('reset')">
              {{ $t("réinitialiser") | capitalize }}
            </b-button>
          </b-button-group>
          <b-button variant="success" type="submit" :disabled="loading" v-else>
            {{ $t("enregistrer") | capitalize }}
          </b-button>
        </div>
      </b-form>
    </validation-observer>
  </div>
</template>

<script>
import FormsValidatedInput from "@/components/Forms/ValidatedInput.vue";

import FormLabelsMixin from "@/mixins/FormLabelsMixin";

import locales from "@/locales";

export default {
  name: "ProfileForm",
  mixins: [FormLabelsMixin],
  components: {
    FormsValidatedInput,
  },
  props: {
    changed: {
      type: Boolean,
      required: false,
      default: false,
    },
    form: {
      type: Object,
      required: true,
    },
    hideButtons: {
      type: Boolean,
      required: false,
    },
    loading: {
      type: Boolean,
      required: true,
    },
    showReset: {
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
    const from = new Date();
    from.setDate(from.getDate() - 1);

    return {
      isPerson: true,
      openDate: new Date("1985-01-01"),
      datesInTheFuture: {
        from,
      },
    };
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.users,
        ...locales.en.forms,
      },
      fr: {
        ...locales.fr.users,
        ...locales.fr.forms,
      },
    },
  },
  computed: {
    age() {
      const age = this.$dayjs(this.$dayjs()).diff(this.user.date_of_birth, "year");

      if (Number.isNaN(age)) {
        return Number.MAX_SAFE_INTEGER;
      }

      return age;
    },
    dateOfBirthRules() {
      return {
        ...this.form.general.date_of_birth.rules,
        required: true,
      };
    },
  },
  methods: {
    onlyChars(event) {
      if (!this.isPerson) {
        return true;
      }

      if (event.key.match(/[0-9]/)) {
        event.preventDefault();
        return false;
      }

      return true;
    },
    submit(...params) {
      this.$emit("submit", ...params);
    },
  },
};
</script>

<style lang="scss"></style>
