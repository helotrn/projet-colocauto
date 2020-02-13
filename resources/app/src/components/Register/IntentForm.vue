<template>
  <div class="register-intent-form">
    <b-form :novalidate="true" class="register-intent-form__form"
      @submit.stop.prevent="$emit('submit')">
      <div class="form__section">
        <b-form-checkbox
          id="car_intent" name="car_intent"
          :value="true"
          :unchecked-value="false"
          v-model="carIntent">
          {{ $t('car-intent') }}
        </b-form-checkbox>

        <b-collapse id="car-intent-section" role="tabpanel" v-model="carIntent">
          Form
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
          Form
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
  car-intent: Je désire emprunter une voiture
  continue: Continuer
  owner-intent: Je désire mettre un véhicule à disposition de la communauté
</i18n>

<script>
import helpers from '@/helpers';

const { buildComputed } = helpers;

export default {
  name: 'RegisterIntentForm',
  props: {
    user: {
      type: Object,
      required: true,
    },
  },
  computed: {
    ...buildComputed('register.intent', ['carIntent', 'ownerIntent']),
  },
};
</script>

<style lang="scss">
</style>
