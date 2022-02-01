<template>
  <div class="profile-form">
    <validation-observer ref="profileFormObserver" v-slot="{ passes }">
      <b-form novalidate class="profile-form__form form" @submit.stop.prevent="passes(submit)">
        <b-row>
          <b-col>
            <forms-validated-input
              name="name"
              label="Quel est votre prénom?*"
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
              label="Nom de famille*"
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

            <forms-validated-input
              name="description"
              description="Vos passions, votre film préféré ou vos plus grandes folies! Ce texte permet à vos voisins de vous découvrir sous un autre angle."
              :rules="{ required: true }"
              label="On brise la glace? Parlez-nous de vous.*"
              type="textarea"
              v-model="user.description"
            />
          </b-col>
        </b-row>

        <b-row>
          <b-col class="profile-picture-uploader">
            <h2>Une petite photo de profil?*</h2>
            <div class="circle">
              <forms-validated-input
                type="image"
                name="avatar"
                :rules="{ required: true }"
                v-model="user.avatar"
              />
            </div>
          </b-col>
        </b-row>

        <b-row class="safety-questions">
          <b-col>
            <h2>Encore quelques questions pour votre sécurité.</h2>
            <h3>
              LocoMotion et son partenaire d’assurance Desjardins ont besoin de ces informations.
            </h3>
          </b-col>
        </b-row>

        <b-row>
          <b-col>
            <forms-validated-input
              name="date_of_birth"
              :label="$t('fields.date_of_birth') | capitalize"
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

        <b-row>
          <b-col>
            <forms-validated-input
              name="phone"
              :label="$t('fields.phone') | capitalize"
              description="Nous permet de vous mettre en contact avec votre interlocuteur
uniquement dans le cadre d’une réservation."
              :rules="form.general.phone.rules"
              type="text"
              mask="(###) ###-####"
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
              name="address"
              :label="$t('fields.address') | capitalize"
              :rules="form.general.address.rules"
              description="Elle nous permet de vous affecter au bon quartier
et ne sera jamais divulguée aux utilisateurs."
              type="text"
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
              v-model="user.postal_code"
            />
          </b-col>
        </b-row>

        <slot />

        <div class="form__buttons" v-if="!hideButtons">
          <b-button-group v-if="showReset">
            <b-button variant="primary" type="submit" :disabled="!changed || loading">
              {{ $t("enregistrer") | capitalize }}
            </b-button>
            <b-button type="reset" :disabled="!changed || loading" @click="$emit('reset')">
              {{ $t("réinitialiser") | capitalize }}
            </b-button>
          </b-button-group>
          <b-button variant="primary" type="submit" :disabled="loading" v-else>
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

import SmilingHeart from "@/assets/svg/smiling-heart.svg";

export default {
  name: "ProfileForm",
  mixins: [FormLabelsMixin],
  components: {
    FormsValidatedInput,
    "svg-smiling-heart": SmilingHeart,
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

<style lang="scss">
.profile-form {
  label {
    color: #1a1a1a;
    font-weight: 400;
    font-size: 14px;
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
    h2 {
      margin: 20px 0;
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
