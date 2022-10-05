<template>
  <div class="profile-form">
    <validation-observer ref="profileFormObserver" v-slot="{ passes, pristine }">
      <b-form class="profile-form__form form" @submit.stop.prevent="passes(submit)">
        <b-row>
          <b-col>
            <forms-validated-input
              name="name"
              :label="($t('fields.name') + '*') | capitalize"
              :rules="form.general.name.rules"
              type="text"
              @keypress.native="onlyChars"
              v-model="user.name"
            />
          </b-col>
        </b-row>

        <b-row>
          <b-col>
            <forms-validated-input
              name="last_name"
              :label="($t('fields.last_name') + '*') | capitalize"
              :rules="{ required: true }"
              type="text"
              v-model="user.last_name"
            />
          </b-col>
        </b-row>

        <b-row>
          <b-col>
            <h2 class="allo-title">
              <svg-smiling-heart />
              Allo {{ user.name }}<span v-if="user.name">,</span>
            </h2>

            <label>On brise la glace? Parlez-nous de vous.*</label>
            <forms-validated-input
              name="description"
              description="Vos passions, votre film préféré ou vos plus grandes folies! Ce texte permet à vos voisin-e-s de vous découvrir sous un autre angle."
              :rules="{ required: true }"
              label="Brise glace"
              type="textarea"
              v-model="user.description"
              class="hide-label"
            />
          </b-col>
        </b-row>

        <b-row>
          <b-col class="profile-picture-uploader">
            <div class="circle">
              <forms-validated-input
                type="image"
                label="Une photo de profil?*"
                :rules="{ required: true }"
                name="avatar"
                v-model="user.avatar"
              />
            </div>
          </b-col>
        </b-row>

        <b-row class="safety-questions">
          <b-col>
            <h2>Quelques questions pour votre sécurité.</h2>
            <h3>
              LocoMotion et son partenaire d’assurance Desjardins ont besoin de ces informations.
            </h3>
          </b-col>
        </b-row>

        <b-row>
          <b-col>
            <forms-validated-input
              name="date_of_birth"
              :label="($t('fields.date_of_birth') + '*') | capitalize"
              description="Cette information est uniquement pour notre assureur"
              :rules="dateOfBirthRules"
              type="date"
              initial-view="year"
              :open-date="openDate"
              :disabled-dates="datesInTheFuture"
              v-model="user.date_of_birth"
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
              name="phone"
              :label="($t('fields.phone') + '*') | capitalize"
              description="Nous permet de vous mettre en contact avec votre interlocuteur
uniquement dans le cadre d’une réservation."
              :rules="form.general.phone.rules"
              type="text"
              mask="(###) ###-####"
              v-model="user.phone"
            />
          </b-col>
        </b-row>

        <b-row>
          <b-col>
            <label>Adresse complète*</label>

            <validation-provider
              :rules="{ isFromGoogleValidation: true }"
              name="Adresse complète*"
              ref="addressValidator"
              v-slot="{ validated, valid, errors, validate }"
              :detect-input="false"
            >
              <gmap-autocomplete
                class="form-control"
                v-bind:class="{ 'is-invalid': validated && !valid, 'is-valid': validated && valid }"
                @place_changed="(e) => setLocation(e.formatted_address, true)"
                :component-restrictions="{ country: 'ca' }"
                :options="{ language: 'fr', fields: ['formatted_address'] }"
                :types="['street_address', 'premise', 'subpremise']"
                placeholder=""
                :value="user.address"
                @blur="() => onLocationBlur(validate)"
                @input="(e) => setLocation(e.target.value, false)"
              >
              </gmap-autocomplete>
              <b-form-invalid-feedback :state="validated ? valid : null">
                {{ errors[0] }}
              </b-form-invalid-feedback>
            </validation-provider>

            <small class="text-muted"
              >Elle nous permet de vous affecter au bon quartier et ne sera jamais divulguée aux
              utilisateurs.</small
            >
          </b-col>
        </b-row>

        <!-- TODO 
        <b-row v-if="mainCommunity">
          <b-col
            ><div class="md-3">
              Vous êtes actuellement inscrit au sein du quartier
              <strong>{{ mainCommunity.name }}</strong
              >.</br></br>
            </div>
          </b-col>
        </b-row> -->

        <slot />

        <div class="form__buttons" v-if="!hideButtons">
          <b-button variant="primary" type="submit" :disabled="loading || pristine">
            {{ $t("enregistrer") | capitalize }}
          </b-button>
          <layout-loading inline v-if="loading" />
        </div>
      </b-form>
    </validation-observer>
  </div>
</template>

<script>
import FormsValidatedInput from "@/components/Forms/ValidatedInput.vue";

import FormLabelsMixin from "@/mixins/FormLabelsMixin";

import locales from "@/locales";

import SmilingHeart from "@/assets/svg/smiling-heart.svg";

import { extend } from "vee-validate";

export default {
  name: "ProfileForm",
  mixins: [FormLabelsMixin],
  components: {
    FormsValidatedInput,
    "svg-smiling-heart": SmilingHeart,
  },
  mounted() {
    // Add custom validation for the address.
    extend("isFromGoogleValidation", {
      validate: ({ address, isFromGoogle }) => isFromGoogle && address !== "",
      message: "L'adresse doit provenir de la liste de suggestions.",
    });
    const initialValue = {
      address: this.user.address,
      // We assume that initially created addresses are correct.
      isFromGoogle: !!this.user.address,
    };
    this.$refs.addressValidator.initialValue = initialValue;
    this.$refs.addressValidator.syncValue(initialValue);
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
      submitted: false,
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
    setLocation(address, isFromGoogle) {
      this.$refs.addressValidator.setFlags({ pristine: false });
      this.user.address = address;
      this.$refs.addressValidator.syncValue({ address, isFromGoogle });
    },
    onLocationBlur(validate) {
      // This timeout lets the setLocation callback run first, which is necessary
      // if the blur happened when the user selected an address from the list
      setTimeout(() => validate(), 200);
    },
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
      this.submitted = true;
      this.loading = true;
    },
  },
};
</script>

<style lang="scss">
.profile-form {
  label {
    color: #1a1a1a;
    font-weight: 400;
    font-size: 14px;
  }

  .hide-label {
    label {
      display: none !important;
    }
  }

  h2.allo-title {
    text-align: left;
    margin: 15px 0 25px 0;
    svg {
      width: 60px;
      position: relative;
      top: -3px;
    }
  }

  .profile-picture-uploader {
    text-align: center;
    label {
      margin: 20px 0;
      font-size: $h3-font-size;
      line-height: $h3-line-height;
    }
    figure {
      height: 200px;
      width: 200px;
      background-color: #00ada8;
      border-radius: 50%;
      display: inline-block;
      overflow: hidden;
      margin: 0 !important;
    }
    .custom-file-label {
      border: none;
      background-image: none;
      height: 200px;
      width: 200px;
      background-color: #00ada8;
      border-radius: 50%;
      margin: 0 auto;
      overflow: inherit;
      background-image: url("/white-female-face.svg");
      &::after {
        background-color: #245ae9;
        border-radius: 32px;
        font-size: 16px;
        color: white;
        bottom: 1rem;
        font-weight: 600;
        z-index: 4;
      }
    }

    button {
      display: block;
      margin: 20px auto;
    }
  }

  .form__buttons {
    margin-top: 20px;
    .btn-primary {
      margin-left: 0;
    }
  }

  .safety-questions {
    h2,
    h3 {
      color: #047249;
    }

    h3 {
      font-size: 18px;
    }
  }
}
</style>
