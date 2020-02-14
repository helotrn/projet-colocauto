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
          <borrower-form v-if="borrower" :borrower="borrower" :loading="loading" />
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
              Bonne nouvelle! Sur votre tableau de bord, vous serez invités à ajouter votre voiture.
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
  car_borrower_intent: Je désire emprunter une voiture
  continue: Continuer
  owner-intent: Je désire mettre un véhicule à disposition de la communauté
</i18n>

<script>
import BorrowerForm from '@/components/Borrower/Form.vue';

import helpers from '@/helpers';

const { buildComputed } = helpers;

export default {
  name: 'RegisterIntentForm',
  components: {
    BorrowerForm,
  },
  mounted() {
    if (this.user.borrower && this.user.borrower.id) {
      this.borrower = this.user.borrower;
      this.carBorrowerIntent = true;
    }
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
  computed: {
    ...buildComputed('register.intent', ['borrower', 'carBorrowerIntent', 'ownerIntent']),
  },
  watch: {
    borrower: {
      deep: true,
      handler(borrower) {
        if (this.carBorrowerIntent) {
          this.$store.commit('users/mergeItem', { borrower, });
        }
      },
    },
    carBorrowerIntent(val) {
      if (val) {
        this.$store.commit('users/mergeItem', { borrower: this.borrower, });
      } else {
        this.$store.commit('users/mergeItem', { borrower: null, });
      }
    },
    ownerIntent(val) {
      if (val) {
        this.$store.commit('users/mergeItem', { owner: {}, });
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
