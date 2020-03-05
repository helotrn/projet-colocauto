<template>
  <div class="profile-form">
    <validation-observer ref="observer" v-slot="{ passes }">
      <b-form :novalidate="true" class="profile-form__form"
        @submit.stop.prevent="passes(submit)">
        <b-row>
          <b-col lg="4">
            <forms-image-uploader field="avatar"
              :label="$t('fields.avatar') | capitalize"
              v-model="user.avatar" />
          </b-col>
          <b-col>
            <b-row>
              <b-col lg="6">
                <forms-validated-input name="name" :label="$t('fields.name') | capitalize"
                  :rules="{ required: true }" type="text"
                  :placeholder="placeholderOrLabel('name') | capitalize"
                  v-model="user.name" />
              </b-col>

              <b-col lg="6">
                <forms-validated-input name="last_name"
                  :label="$t('fields.last_name') | capitalize"
                  :rules="{ required: true }" type="text"
                  :placeholder="placeholderOrLabel('last_name') | capitalize"
                  v-model="user.last_name" />
              </b-col>
            </b-row>

            <b-row>
              <b-col>
                <forms-validated-input name="description"
                  :description="$t('descriptions.description')"
                  :label="$t('fields.description') | capitalize" type="textarea"
                  :placeholder="placeholderOrLabel('description') | capitalize"
                  v-model="user.description" />
              </b-col>
            </b-row>
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
              :rules="{ required: true }" type="date"
              :placeholder="placeholderOrLabel('date_of_birth') | capitalize"
              v-model="user.date_of_birth" />
          </b-col>
        </b-row>

        <b-row>
          <b-col>
            <forms-validated-input name="address" :label="$t('fields.address') | capitalize"
              :rules="{ required: true }" type="text"
              :placeholder="placeholderOrLabel('address') | capitalize"
              v-model="user.address" />
          </b-col>
        </b-row>

        <b-row>
          <b-col>
            <forms-validated-input name="postal_code"
              :label="$t('fields.postal_code') | capitalize"
              :rules="{
                required: true,
                regex: /^[a-zA-Z][0-9][a-zA-Z]\s*[0-9][a-zA-Z][0-9]$/
              }" type="text"
              :placeholder="placeholderOrLabel('postal_code') | capitalize"
              v-model="user.postal_code" />
          </b-col>
        </b-row>

        <b-row>
          <b-col md="8">
            <forms-validated-input name="phone" :label="$t('fields.phone') | capitalize"
              :rules="{ required: true, regex:/^[-1-9][-0-9]*$/  }" type="text"
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

        <b-row>
          <b-col>
            <forms-validated-input name="other_phone"
              :label="$t('fields.other_phone') | capitalize"
              :rules="{ regex:/^[-1-9][-0-9]*$/  }" type="text"
              :placeholder="placeholderOrLabel('other_phone') | capitalize"
              v-model="user.other_phone" />
          </b-col>
        </b-row>

        <div class="form__buttons" v-if="!hideButtons">
          <b-button-group v-if="showReset">
            <b-button variant="success" type="submit" :disabled="!changed">
              {{ $t('enregistrer') | capitalize }}
            </b-button>
            <b-button type="reset" :disabled="!changed" @click="$emit('reset')">
              {{ $t('réinitialiser') | capitalize }}
            </b-button>
          </b-button-group>
          <b-button variant="success" type="submit" v-else>
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

import locales from '@/locales';

export default {
  name: 'ProfileForm',
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
    placeholderOrLabel(key) {
      if (this.$i18n.te(`placeholders.${key}`)) {
        return this.$i18n.t(`placeholders.${key}`);
      }

      return this.label(key);
    },
    label(key) {
      return this.$i18n.t(`fields.${key}`);
    },
    getValidationState({ dirty, validated, valid = null }) {
      return dirty || validated ? valid : null;
    },
    submit(...params) {
      this.$emit('submit', ...params);
    },
  },
};
</script>

<style lang="scss">
</style>
