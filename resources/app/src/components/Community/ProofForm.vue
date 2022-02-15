<template>
  <div class="community-proof-form">
    <validation-observer ref="observer" v-slot="{ passes }">
      <b-form
        :novalidate="true"
        class="community-proof-form__form"
        @submit.stop.prevent="passes(submit)"
      >
        <b-row>
          <b-col>
            <forms-image-uploader field="proof" v-model="community.proof" />
          </b-col>
        </b-row>
        <b-row>
          <b-col>
            <div class="community-proof-form__instructions">
              <p>
                <strong
                  >Merci de téléverser ou prendre une photo avec votre téléphone une preuve de
                  résidence.</strong
                >
              </p>

              <p>
                Elle doit contenir votre nom, votre adresse et la date. Elle doit dater de moins
                d'un an ou ne pas être expirée. Au format JPG ou PNG. Nous n'acceptons pas encore
                les PDF.
              </p>

              <p><strong>Exemples de documents acceptés :</strong></p>

              <ul>
                <li>✅ Permis de conduire valide (recto et verso si nouvelle adresse au verso);</li>
                <li>
                  ✅ Facture récente d'une institution reconnue (électricité, gaz,
                  télécommunications);
                </li>
                <li>✅ Document émis par un gouvernement fédéral, provincial ou municipal;</li>
                <li>✅ Document officiel d’une institution bancaire ou de crédit;</li>
                <li>✅ Bulletin scolaire ou relevé de notes.</li>
              </ul>

              <p><strong>Pièces non acceptées :</strong></p>

              <ul>
                <li>❌ bail;</li>
                <li>❌ facture d'un service professionnel (avocat, notaire, etc.);</li>
                <li>❌ carte professionnelle;</li>
                <li>❌ carte d'autobus sans photo;</li>
                <li>❌ carte de crédit.</li>
              </ul>
            </div>

            <div class="community-proof-form__requirements">
              {{ community.requirements }}
            </div>

            <div class="community-proof-form__buttons">
              <b-button variant="primary" type="submit" :disabled="loading"> Soumettre </b-button>
              <b-button variant="outline-primary" type="submit" to="/register/4" class="later-btn">
                Plus tard</b-button
              >
            </div>
          </b-col>
        </b-row>
      </b-form>
    </validation-observer>
  </div>
</template>

<script>
import FormsImageUploader from "@/components/Forms/ImageUploader.vue";

import locales from "@/locales";

export default {
  name: "CommunityProofForm",
  components: {
    FormsImageUploader,
  },
  props: {
    community: {
      type: Object,
      required: true,
    },
    loading: {
      type: Boolean,
      required: false,
      default: false,
    },
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.communities,
        ...locales.en.forms,
      },
      fr: {
        ...locales.fr.communities,
        ...locales.fr.forms,
      },
    },
  },
  methods: {
    submit(...params) {
      this.$emit("submit", ...params);
    },
  },
};
</script>

<style lang="scss">
.community-proof-form {
  &__instructions {
    ul {
      list-style-type: none;
      margin-top: 20px;
      padding-left: 0;
      li {
        margin-bottom: 10px;
      }
    }
  }
  &__buttons {
    margin-top: 30px;
    button {
      margin-left: 0;
    }
  }
}
</style>
