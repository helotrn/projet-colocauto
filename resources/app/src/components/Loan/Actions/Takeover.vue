<template>
  <b-card no-body class="loan-form loan-actions loan-actions-takeover">
    <b-card-header header-tag="header" role="tab" class="loan-actions__header"
      v-b-toggle.loan-actions-takeover>
      <h2>
        <svg-waiting v-if="action.status === 'in_process'" />
        <svg-check v-else-if="action.status === 'completed'" />
        <svg-danger v-else-if="action.status === 'canceled'" />

        Informations avant de partir
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
        <b-row>
          <b-col>
            <b-jumbotron bg-variant="warning"
              header="COVID-19 | Informations"
              lead="Vous êtes malade ou vous revenez de voyage? N'utilisez pas LocoMotion">
              <dl>
                <dt>Pour les propriétaires d'auto</dt>
                <dd>
                  Si vous souhaitez retirer temporairement votre auto,
                  pensez à mettre à jour ses disponibilités.
                </dd>

                <dt>Pour tout le monde</dt>
                <dd>
                  Avant et après l’utilisation d’un vélo, d'une remorque ou d'une auto,
                  <strong>lavez vos mains</strong> à l’eau courante tiède et au savon pendant
                  au moins 20 secondes ou utilisez un désinfectant à base d’alcool.<br>
                  Pendant un trajet à vélo, respectez les 2 mètres de distance.
                </dd>

                <dt>Gardez les véhicules propres</dt>
                <dd>
                  Nettoyez le volant, le guidon ou toutes autres surfaces de contact
                  avec un linge et du désinfectant. Évitez plus que jamais de laisser tout
                  déchet dans les autos et les remorques  (mouchoir, tasse, emballage, etc…).
                </dd>
              </dl>
            </b-jumbotron>
          </b-col>
        </b-row>

        <div v-if="item.loanable.type === 'car'">
          <validation-observer ref="observer" v-slot="{ passes }">
            <b-row v-if="userRole === 'borrower'">
              <b-col>
                <p>
                  Avez-vous bien pris connaissance de l'état de cette auto?<br>
                  Il est important de le faire avant de prendre en prendre possession.
                  Ainsi, s'il arrive un pépin, vous allez être en mesure d'en discuter avec
                  la personne propriétaire de l'auto.
                </p>
              </b-col>
            </b-row>
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
                    On vous demande une preuve? Prenez une photo du tableau de bord de l'auto
                    pour rentrer les bonnes informations (kilométrage, essence).
                    Cette photo est facultative.
                  </small></p>
                </b-col>
                <b-col lg="6" v-else-if="action.image">
                  <a href="#" v-b-modal="'takeover-image'">
                    <img :src="action.image ? action.image.sizes.thumbnail : ''">
                  </a>

                  <b-modal size="xl"
                    title="Photo du tableau de bord"
                    :id="'takeover-image'" footer-class="d-none">
                    <img class="img-fit" :src="action.image.url">
                  </b-modal>
                </b-col>

                <b-col lg="6">
                  <forms-validated-input
                    id="mileage_beginning" name="mileage_beginning"
                    type="number" :rules="{ required: true }"
                    label="KM au compteur, au début de la course"
                    placholder="KM au compteur"
                    :disabled="!!action.executed_at"
                    v-model="action.mileage_beginning" />
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

              <b-row class="loan-actions__alert" v-if="!action.executed_at">
                <b-col>
                  <b-alert variant="warning" show>
                    Dans 48h, vous ne pourrez plus modifier vos informations.
                    Nous validerons le coût de l'emprunt avec les détails ci-dessus.
                  </b-alert>
                </b-col>
              </b-row>
            </b-form>
          </validation-observer>
        </div>

        <div v-else-if="item.loanable.has_padlock">
          <p>
            Le cadenas du véhicule sera automatiquement associé à votre application NOKE
            à temps pour la prise de possession.
          </p>

          <b-alert show variant="info">
            <p>Vous avez un problème avec le cadenas?</p>
            <p>Contactez-nous entre 9h et 20h au 438-476-3343</p>
            <p>(cette ligne est dédiée uniquement aux problèmes liés aux cadenas)</p>
          </b-alert>

          <validation-observer ref="observer" v-slot="{ passes }">
            <b-form :novalidate="true" class="register-form__form"
              @submit.stop.prevent="passes(completeAction)">
              <b-row v-if="!action.executed_at">
                <b-col>
                  <p>Envoyez une photo de l'état du véhicule.</p>

                  <forms-image-uploader
                    label="Photo du véhicule"
                    field="image"
                    v-model="action.image" />

                  <p><small>
                    Cette photo est optionnelle mais permet à LocoMotion de déterminer à quel
                    moment un bris s'est produit, le cas échéant.
                  </small></p>
                </b-col>
              </b-row>
              <b-row v-else-if="action.image">
                <b-col>
                  <a href="#" v-b-modal="'takeover-image'">
                    <img :src="action.image ? action.image.sizes.thumbnail : ''">
                  </a>

                  <b-modal size="xl"
                    title="Photo de l'état du véhicule"
                    :id="'takeover-image'" footer-class="d-none">
                    <img class="img-fit" :src="action.image.url">
                  </b-modal>
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

              <b-row class="loan-actions__alert" v-if="!action.executed_at">
                <b-col>
                  <b-alert variant="warning" show>
                    Dans 48h, vous ne pourrez plus modifier vos informations.
                    Nous validerons le coût de l'emprunt avec les détails ci-dessus.
                  </b-alert>
                </b-col>
              </b-row>
            </b-form>
          </validation-observer>
        </div>

        <div v-else>
          <b-row v-if="!action.executed_at">
            <b-col>
              <p v-if="userRole === 'borrower'">
                Demandez au propriétaire de récupérer le véhicule.
              </p>
              <p v-else>
                L'emprunteur vous contactera pour arranger la prise de possession du véhicule.
              </p>
            </b-col>
          </b-row>
          <b-row v-else>
            <b-col>
              <p v-if="action.status !== 'canceled'">
                La prise de possession a été effectuée.
              </p>
              <p v-else>
                La prise de possession a été annulée.
              </p>
            </b-col>
          </b-row>

          <b-row class="loan-actions-takeover__buttons text-center"
            v-if="!action.executed_at">
            <b-col>
              <b-button type="submit" size="sm" variant="success"
                class="mr-3"
                @click="completeAction">
                C'est fait!
              </b-button>
            </b-col>
          </b-row>

          <b-row class="loan-actions__alert" v-if="!action.executed_at">
            <b-col>
              <b-alert variant="warning" show>
                Dans 48h, vous ne pourrez plus modifier vos informations.
                Nous validerons le coût de l'emprunt avec les détails ci-dessus.
              </b-alert>
            </b-col>
          </b-row>
        </div>

        <div v-if="isContestable">
          <hr>

          <b-row>
            <b-col lg="6">
              <p>
                Cette information est-elle incorrecte?
              </p>
              <p>
                Pour la modifier, vous pouvez procéder
                à une "contestation". Par cette procédure, un membre de l'équipe LocoMotion
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
      </b-collapse>
    </b-card-body>
  </b-card>
</template>

<script>
import FormsImageUploader from '@/components/Forms/ImageUploader.vue';
import FormsValidatedInput from '@/components/Forms/ValidatedInput.vue';

import LoanActionsMixin from '@/mixins/LoanActionsMixin';

export default {
  name: 'LoanActionsTakeover',
  mixins: [LoanActionsMixin],
  components: {
    FormsImageUploader,
    FormsValidatedInput,
  },
};
</script>

<style lang="scss">
</style>
