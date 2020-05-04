<template>
  <div class="profile-form">
    <validation-observer ref="observer" v-slot="{ passes }">
      <b-form novalidate class="profile-form__form form"
        @submit.stop.prevent="passes(submit)">
        <b-row>
          <b-col lg="4">
            <forms-image-uploader field="avatar"
              label="📷 Ajouter une photo de profil"
              v-model="user.avatar" />
          </b-col>
          <b-col>
            <b-row>
              <b-col lg="6">
                <forms-validated-input name="name" :label="$t('fields.name') | capitalize"
                  :rules="form.general.name.rules" type="text" @keypress.native="onlyChars"
                  :placeholder="placeholderOrLabel('name') | capitalize"
                  v-model="user.name" />
              </b-col>

              <b-col lg="6">
                <forms-validated-input name="last_name"
                  :label="$t('fields.last_name') | capitalize"
                  :rules="form.general.last_name.rules" type="text"
                  :placeholder="placeholderOrLabel('last_name') | capitalize"
                  v-model="user.last_name" />
              </b-col>
            </b-row>

            <b-row>
              <b-col>
                <forms-validated-input name="description"
                  :description="$t('descriptions.description')"
                  label="Pour briser la glace" type="textarea"
                  :placeholder="placeholderOrLabel('description') | capitalize"
                  v-model="user.description" />
              </b-col>
            </b-row>
          </b-col>
        </b-row>

        <hr>

        <b-alert variant="warning" show>
          À partir d'ici, les données que vous entrez seront uniquement partagées avec
          l'équipe de Locomotion ou si vous mettez vos véhicules à disposition de la
          communauté.<br>
          Consultez notre <router-link to="/privacy">politique de confidentialité</router-link>.
        </b-alert>

        <b-row>
          <b-col md="8">
            <forms-validated-input name="phone" :label="$t('fields.phone') | capitalize"
              :rules="form.general.phone.rules" type="text"
              mask="(###) ###-####"
              :placeholder="placeholderOrLabel('phone') | capitalize"
              v-model="user.phone" />
          </b-col>

          <b-col md="4">
            <forms-validated-input name="is_smart_phone"
              :label="$t('fields.is_smart_phone') | capitalize" type="checkbox"
              :placeholder="placeholderOrLabel('is_smart_phone') | capitalize"
              v-model="user.is_smart_phone" />
          </b-col>
        </b-row>

        <hr>

        <b-alert variant="warning" show>
          À partir d'ici, les données que vous entrez sont strictement confidentielles.<br>
          Consultez notre <router-link to="/privacy">politique de confidentialité</router-link>.
        </b-alert>

        <b-row>
          <b-col>
            <forms-validated-input name="date_of_birth"
              :label="$t('fields.date_of_birth') | capitalize"
              :rules="form.general.date_of_birth.rules" type="date" initial-view="year"
              :placeholder="placeholderOrLabel('date_of_birth') | capitalize"
              :open-date="openDate" :disabled-dates="datesInTheFuture"
              v-model="user.date_of_birth" />
          </b-col>
        </b-row>

        <b-row>
          <b-col>
            <forms-validated-input name="address" :label="$t('fields.address') | capitalize"
              :rules="form.general.address.rules" type="text"
              :placeholder="placeholderOrLabel('address') | capitalize"
              v-model="user.address" />
          </b-col>
        </b-row>

        <b-row>
          <b-col>
            <forms-validated-input name="postal_code"
              :label="$t('fields.postal_code') | capitalize"
              :rules="form.general.postal_code.rules" type="text" mask="A#A #A#"
              :placeholder="placeholderOrLabel('postal_code') | capitalize"
              v-model="user.postal_code" />
          </b-col>
        </b-row>

        <b-row>
          <b-col>
            <forms-validated-input name="other_phone"
              :label="$t('fields.other_phone') | capitalize"
              :rules="form.general.other_phone.rules" type="text" mask="(###) ###-####"
              :placeholder="placeholderOrLabel('other_phone') | capitalize"
              v-model="user.other_phone" />
          </b-col>
        </b-row>

        <div class="form__buttons" v-if="!hideButtons">
          <b-button-group v-if="showReset">
            <b-button variant="success" type="submit" :disabled="!changed || loading">
              {{ $t('enregistrer') | capitalize }}
            </b-button>
            <b-button type="reset" :disabled="!changed || loading" @click="$emit('reset')">
              {{ $t('réinitialiser') | capitalize }}
            </b-button>
          </b-button-group>
          <b-button variant="success" type="submit" :disabled="loading" v-else>
            {{ $t('enregistrer') | capitalize }}
          </b-button>
        </div>
      </b-form>
    </validation-observer>
  </div>
</template>

<script>
import FormsValidatedInput from '@/components/Forms/ValidatedInput.vue';
import FormsImageUploader from '@/components/Forms/ImageUploader.vue';

import FormLabelsMixin from '@/mixins/FormLabelsMixin';

import locales from '@/locales';

export default {
  name: 'ProfileForm',
  mixins: [FormLabelsMixin],
  components: {
    FormsImageUploader,
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
      openDate: new Date('1985-01-01'),
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
      this.$emit('submit', ...params);
    },
  },
};
</script>

<style lang="scss">
</style>
