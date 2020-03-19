<template>
  <b-card no-body class="loan-form loan-actions loan-actions-takover">
    <b-card-header header-tag="header" role="tab" class="loan-actions__header">
      <h2 v-b-toggle.loan-actions-takover>
        <svg-waiting v-if="action.status === 'in_process'" />
        <svg-check v-else-if="action.status === 'completed'" />
        <svg-danger v-else-if="action.status === 'canceled'" />

        Prise de possession
      </h2>

      <span v-if="action.status == 'in_process'">En attente</span>
      <span v-else-if="action.status === 'completed'">
        Rempli &bull; {{ action.executed_at | datetime }}
      </span>
      <span v-else-if="action.status === 'canceled'">
        Contesté &bull; {{ action.executed_at | datetime }}
      </span>
    </b-card-header>

    <b-card-body>
      <b-collapse id="loan-actions-takover" role="tabpanel" accordion="loan-actions"
        :visible="open">
        <div>
          <b-row>
            <b-col lg="6" v-if="!action.executed_at">
              <p>Envoyez une photo du tableau de bord.</p>

              <forms-image-uploader
                label="Photo du tableau de bord"
                field="image"
                v-model="action.image" />

              <p><small>
                Cette photo doit indiquer le kilométrage de départ et le niveau d'essence.
              </small></p>
            </b-col>
            <b-col lg="6" v-else>
              <img :src="action.image ? action.image.sizes.thumbnail : ''">
            </b-col>

            <b-col lg="6">
              <forms-validated-input
                id="mileage_beginning" name="mileage_beginning"
                type="number"
                label="KM au compteur, au début de la course"
                placholder="KM au compteur"
                :disabled="!!action.executed_at"
                v-model="action.mileage_beginning" />

              <forms-validated-input
                id="fuel_beginning" name="fuel_beginning"
                type="text"
                label="Essence dans le réservervoir au début de la course"
                placeholder="Donnez une indication approximative"
                :disabled="!!action.executed_at"
                v-model="action.fuel_beginning" />
            </b-col>
          </b-row>

          <b-row class="loan-actions-takeover__buttons text-center"
            v-if="!action.executed_at">
            <b-col>
              <b-button size="sm" variant="success" class="mr-3" @click="completeAction">
                Enregistrer
              </b-button>
            </b-col>
          </b-row>
        </div>
        <b-row>
          <b-col lg="6">
            <p>
              Cette information est-elle incorrecte?
            </p>
            <p>
              Pour la modifier, vous pouvez procéder
              à une "contestation". Par cette procédure, un membre de l'équipe Locomotion
              sera appelé à arbitrer la résolution du conflit entre l'emprunteur et le
              propriétaire.
            </p>
          </b-col>

          <b-col lg="6">
            <forms-validated-input
              id="comments_on_contestation" name="comments_on_contestation"
              type="text"
              label="Commentaires sur la contestation"
              placeholder="Commentaire sur la contestation"
              v-model="action.comments_on_contestation" />
          </b-col>
        </b-row>

        <b-row class="loan-actions-takeover__buttons text-center">
          <b-col>
            <b-button size="sm" variant="outline-danger" @click="cancelAction">
              Contester
            </b-button>
          </b-col>
        </b-row>
      </b-collapse>
    </b-card-body>
  </b-card>
</template>

<script>
import FormsImageUploader from '@/components/Forms/ImageUploader.vue';
import FormsValidatedInput from '@/components/Forms/ValidatedInput.vue';
import UserAddCreditBox from '@/components/User/AddCreditBox.vue';

import LoanActionsMixin from '@/mixins/LoanActionsMixin';

export default {
  name: 'LoanActionsPrePayment',
  mixins: [LoanActionsMixin],
  components: {
    FormsImageUploader,
    FormsValidatedInput,
    UserAddCreditBox,
  },
};
</script>

<style lang="scss">
</style>
