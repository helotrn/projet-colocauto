<template>
  <div class="register-intent-form">
    <b-form
      :novalidate="true"
      class="register-intent-form__form"
      @submit.stop.prevent="$emit('submit')"
    >
      <div class="form__section">
        <b-form-checkbox
          id="car_borrower_intent"
          name="car_borrower_intent"
          :value="true"
          :unchecked-value="false"
          v-model="carBorrowerIntent"
        >
          {{ $t("car_borrower_intent") }}
        </b-form-checkbox>

        <b-collapse
          role="tabpanel"
          v-model="carBorrowerIntent"
          class="register-intent-form__form__car_borrower_intent"
        >
          <div class="register-intent-form__form__car_borrower_intent__text">
            <p>
              Pour utiliser l'auto d'un-e voisin-e, vous devez compléter votre dossier de conduite
              avec les informations ci-dessous.
            </p>
          </div>

          <borrower-form v-if="borrower" :borrower="borrower" :loading="loading" hide-buttons />

          <div class="register-intent-form__form__car_borrower_intent__text">
            <p>Vous ne les avez pas sous la main ? Pas de soucis.</p>

            <ul>
              <li>
                <a :href="saaqUrl" target="_blank">
                  Pour commander votre dossier de conduite SAAQ
                </a>
              </li>
              <li>
                <a :href="gaaUrl" target="_blank"> Pour commander votre rapport de sinistre GAA </a>
              </li>
              <li>Référez-vous à votre assurance pour retrouver votre contrat.</li>
            </ul>

            <p>
              Certains de ces documents doivent être commandés. Commandez-les dès maintenant. Vous
              pourrez compléter votre dossier de conduite plus tard, via "Mon profil".
            </p>

            <p>
              <strong> D'ici là, vous pourrez déjà utiliser les vélos et remorques à vélo. </strong>
            </p>
          </div>
        </b-collapse>
      </div>

      <div class="form__section">
        <b-form-checkbox
          id="owner_intent"
          name="owner_intent"
          :value="true"
          :unchecked-value="false"
          v-model="ownerIntent"
        >
          {{ $t("owner-intent") }}
        </b-form-checkbox>

        <b-collapse
          id="owner-intent-section"
          role="tabpanel"
          v-model="ownerIntent"
          class="register-intent-form__form__owner_intent"
        >
          <div class="register-intent-form__form__owner_intent__text">
            <p>
              Ça fera le bonheur de vos voisin-e-s, c’est génial! Rendez-vous sur le tableau de bord
              pour ajouter votre véhicule.
            </p>
          </div>
        </b-collapse>
      </div>

      <div class="form__section">
        <b-form-checkbox
          id="other_intent"
          name="other_intent"
          :value="true"
          :unchecked-value="false"
          v-model="otherIntent"
        >
          {{ $t("other-intent") }}
        </b-form-checkbox>

        <b-collapse
          id="other-intent-section"
          role="tabpanel"
          v-model="otherIntent"
          class="register-intent-form__form__other_intent"
        >
          <div class="register-intent-form__form__other_intent__text">
            <p>Excellent! Rendez-vous sur la carte pour réserver un véhicule.</p>
          </div>
        </b-collapse>
      </div>

      <div class="form__buttons text-center">
        <b-button variant="success" type="submit" size="lg">
          {{ $t("continue") }}
        </b-button>
      </div>
    </b-form>
  </div>
</template>

<i18n>
fr:
  car_borrower_intent: J'aimerais utiliser une auto
  continue: Terminer
  owner-intent: J'aimerais partager mon auto, mon vélo...
  other-intent: J'aimerais utiliser un vélo ou une remorque à vélo
</i18n>

<script>
import BorrowerForm from "@/components/Borrower/BorrowerForm.vue";

import { buildComputed } from "@/helpers";

export default {
  name: "RegisterIntentForm",
  components: {
    BorrowerForm,
  },
  props: {
    user: {
      type: Object,
      required: true,
    },
    loading: {
      type: Boolean,
      required: true,
    },
  },
  data() {
    return {
      gaaUrl:
        "https://gaa.qc.ca/fr/fichier-central-des-sinistres-automobiles/votre-dossier-de-sinistres/",
      otherIntent: true,
      saaqUrl:
        "https://saaq.gouv.qc.ca/services-en-ligne/citoyens/demander-copie-dossier-conduite/",
    };
  },
  computed: {
    ...buildComputed("register.intent", ["borrower", "carBorrowerIntent", "ownerIntent"]),
  },
  watch: {
    borrower: {
      deep: true,
      handler(borrower) {
        if (this.carBorrowerIntent) {
          this.$store.commit("users/mergeItem", { borrower });
        }
      },
    },
    carBorrowerIntent(val) {
      if (val) {
        this.$store.commit("users/mergeItem", { borrower: this.borrower });
      } else {
        this.$store.commit("users/mergeItem", { borrower: {} });
      }
    },
    ownerIntent(val) {
      if (val) {
        this.$store.commit("users/mergeItem", { owner: {} });
      } else {
        this.$store.commit("users/mergeItem", { owner: null });
      }
    },
  },
};
</script>

<style lang="scss">
.register-intent-form {
  &__form__car_borrower_intent,
  &__form__other_intent,
  &__form__owner_intent {
    padding-left: 1.5rem;
  }

  .form__section {
    > .custom-checkbox {
      color: $locomotion-light-green;
    }

    > .custom-checkbox + .collapse {
      margin-top: 20px;
    }
  }
}
</style>
