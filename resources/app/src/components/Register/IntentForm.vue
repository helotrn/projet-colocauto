<template>
  <div class="register-intent-form">
    <b-form :novalidate="true" class="register-intent-form__form"
      @submit.stop.prevent="$emit('submit')">
      <div class="form__section">
        <b-form-checkbox
          id="car_borrower_intent" name="car_borrower_intent"
          :value="true"
          :unchecked-value="false"
          v-model="carBorrowerIntent">
          {{ $t('car_borrower_intent') }}
        </b-form-checkbox>

        <b-collapse role="tabpanel" v-model="carBorrowerIntent">
          <div class="register-intent-form__form__car_borrower_intent__text">
            <p>
              Pour utiliser l'auto d'une personne du voisinage, vous devez compléter
              <span class="no-break">« vos dossiers »</span> en y ajoutant les documents
              relatifs à votre conduite automobile. Si vous ne les avez pas sous la main,
              pas de panique! Commandez-les dès maintenant. D'ici à ce que vous les recevez,
              vous pouvez toujours utiliser les vélos et remorques à vélo.
            </p>

            <ul>
              <li>
                <a :href="saaqUrl" target="_blank">
                  Pour commander votre dossier de conduite SAAQ
                </a>
              </li>
              <li>
                <a :href="gaaUrl" target="_blank">
                  Pour commander votre rapport de sinistre GAA
                </a>
              </li>
              <li>
                Référez-vous à votre assurance pour retrouver votre contrat.
              </li>
            </ul>
          </div>
          <borrower-form v-if="borrower" :borrower="borrower" :loading="loading" hide-buttons />
        </b-collapse>
      </div>

      <div class="form__section">
        <b-form-checkbox
          id="owner_intent" name="owner_intent"
          :value="true"
          :unchecked-value="false"
          v-model="ownerIntent">
          {{ $t('owner-intent') }}
        </b-form-checkbox>

        <b-collapse id="owner-intent-section" role="tabpanel" v-model="ownerIntent">
          <div class="register-intent-form__owner__text">
            <p>
              Ça fera le bonheur de votre voisinage, c'est génial!
              Rendez-vous sur le tableau de bord pour ajouter votre véhicule.
            </p>
          </div>
        </b-collapse>
      </div>

      <div class="form__section">
        <b-form-checkbox
          id="other_intent" name="other_intent"
          :value="true"
          :unchecked-value="false"
          v-model="otherIntent">
          {{ $t('other-intent') }}
        </b-form-checkbox>

        <b-collapse id="other-intent-section" role="tabpanel" v-model="otherIntent">
          <div class="register-intent-form__other__text">
            <p>
              Excellent! Rendez-vous sur la carte pour trouver un véhicule.
            </p>
          </div>
        </b-collapse>
      </div>

      <div class="form__buttons">
        <b-button variant="success" type="submit">
          {{ $t('continue') }}
        </b-button>
      </div>
    </b-form>
  </div>
</template>

<i18n>
fr:
  car_borrower_intent: J'aimerais utiliser une auto
  continue: Continuer
  owner-intent: Je veux partager mon auto, mon vélo...
  other-intent: Je veux utiliser un vélo ou une remorque à vélo
</i18n>

<script>
import BorrowerForm from '@/components/Borrower/Form.vue';

import { buildComputed } from '@/helpers';

export default {
  name: 'RegisterIntentForm',
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
      gaaUrl: 'https://gaa.qc.ca/fr/fichier-central-des-sinistres-automobiles/votre-dossier-de-sinistres/',
      otherIntent: true,
      saaqUrl: 'https://saaq.gouv.qc.ca/services-en-ligne/citoyens/demander-copie-dossier-conduite/',
    };
  },
  computed: {
    ...buildComputed('register.intent', ['borrower', 'carBorrowerIntent', 'ownerIntent']),
  },
  watch: {
    borrower: {
      deep: true,
      handler(borrower) {
        if (this.carBorrowerIntent) {
          this.$store.commit('users/mergeItem', { borrower });
        }
      },
    },
    carBorrowerIntent(val) {
      if (val) {
        this.$store.commit('users/mergeItem', { borrower: this.borrower });
      } else {
        this.$store.commit('users/mergeItem', { borrower: {} });
      }
    },
    ownerIntent(val) {
      if (val) {
        this.$store.commit('users/mergeItem', { owner: {} });
      } else {
        this.$store.commit('users/mergeItem', { owner: null });
      }
    },
  },
};
</script>

<style lang="scss">
.register-intent-form {
  .form__section {
    > .custom-checkbox + .collapse {
      margin-top: 20px;
    }
  }
}
</style>
