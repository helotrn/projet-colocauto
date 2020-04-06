<template>
  <b-card no-body class="loan-form loan-actions loan-actions-takeover">
    <b-card-header header-tag="header" role="tab" class="loan-actions__header">
      <h2 v-b-toggle.loan-actions-takeover>
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
        Annulé &bull; {{ action.executed_at | datetime }}
      </span>
    </b-card-header>

    <b-card-body>
      <b-collapse id="loan-actions-takeover" role="tabpanel" accordion="loan-actions"
        :visible="open">
        <div v-if="loan.loanable.type === 'car'">
          <validation-observer ref="observer" v-slot="{ passes }">
            <b-form :novalidate="true" class="register-form__form"
              @submit.stop.prevent="passes(completeAction)">
              <b-row>
                <b-col lg="6" v-if="!action.executed_at">
                  <p>Envoyez une photo du tableau de bord.</p>

                  <forms-image-uploader
                    label="Photo du tableau de bord"
                    field="image"
                    v-model="action.image" />

                  <p><small>
                    Cette photo doit indiquer le kilométrage et le niveau d'essence. Elle est
                    optionnelle mais représente la meilleur solution pour éviter toute
                    ambiguïté.
                  </small></p>
                </b-col>
                <b-col lg="6" v-else>
                  <img :src="action.image ? action.image.sizes.thumbnail : ''">
                </b-col>

                <b-col lg="6">
                  <forms-validated-input
                    id="mileage_beginning" name="mileage_beginning"
                    type="number" :rules="{ required: true }"
                    label="KM au compteur, au début de la course"
                    placholder="KM au compteur"
                    :disabled="!!action.executed_at"
                    v-model="action.mileage_beginning" />

                  <forms-validated-input
                    id="fuel_beginning" name="fuel_beginning"
                    type="text" :rules="{ required: true }"
                    label="Essence dans le réservervoir au début de la course"
                    placeholder="Donnez une indication approximative"
                    :disabled="!!action.executed_at"
                    v-model="action.fuel_beginning" />
                </b-col>
              </b-row>

              <b-row class="loan-actions-takeover__buttons text-center"
                v-if="!action.executed_at">
                <b-col>
                  <b-button type="submit" size="sm" variant="success" class="mr-3">
                    Enregistrer
                  </b-button>
                </b-col>
              </b-row>
            </b-form>
          </validation-observer>

          <div v-if="!!action.executed_at && action.status !== 'canceled'">
            <hr>

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
                  type="textarea" :rows="3"
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
          </div>
        </div>
        <div v-else-if="loan.loanable.has_padlock">
          <p>
            Le cadenas du véhicule sera automatiquement associé à votre application NOKE
            à temps pour la prise de possession.
          </p>

          <b-row v-if="!action.executed_at">
            <b-col>
              <p>Envoyez une photo de l'état du véhicule.</p>

              <forms-image-uploader
                label="Photo du tableau de bord"
                field="image"
                v-model="action.image" />

              <p><small>
                Cette photo est optionnelle mais permet à Locomotion de déterminer à quel
                moment un bris s'est produit, le cas échéant.
              </small></p>
            </b-col>
          </b-row>
        </div>
        <div>
          <p>
            Demandez au propriétaire de récupérer le véhicule.
          </p>
        </div>
      </b-collapse>
    </b-card-body>
  </b-card>
</template>

<script>
import FormsImageUploader from '@/components/Forms/ImageUploader.vue';
import FormsValidatedInput from '@/components/Forms/ValidatedInput.vue';

import LoanActionsMixin from '@/mixins/LoanActionsMixin';

export default {
  name: 'LoanActionsPrePayment',
  mixins: [LoanActionsMixin],
  components: {
    FormsImageUploader,
    FormsValidatedInput,
  },
};
</script>

<style lang="scss">
</style>
