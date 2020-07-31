<template>
  <b-card no-body class="loan-form loan-actions loan-actions-handover-collective">
    <b-card-header header-tag="header" role="tab" class="loan-actions__header">
      <h2 v-b-toggle.loan-actions-handover-collective>
        <svg-waiting v-if="action.status === 'in_process'" />
        <svg-check v-else-if="action.status === 'completed'" />
        <svg-danger v-else-if="action.status === 'canceled'" />

        Remise du véhicule
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
      <b-collapse id="loan-actions-handover-collective" role="tabpanel" accordion="loan-actions"
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
                    On vous demande une preuve? Prenez une photo du tableau de bord de l'auto
                    pour rentrer les bonnes informations (kilométrage, essence).
                    Cette photo est facultative.
                  </small></p>
                </b-col>
                <b-col lg="6" v-else-if="action.image">
                  <a href="#" v-b-modal="'handover-collective-image'">
                    <img :src="action.image ? action.image.sizes.thumbnail : ''">
                  </a>

                  <b-modal size="xl"
                    title="Photo du tableau de bord"
                    :id="'handover-collective-image'" footer-class="d-none">
                    <img class="img-fit" :src="action.image.url">
                  </b-modal>
                </b-col>

                <b-col lg="6">
                  <forms-validated-input
                    id="mileage_end" name="mileage_end"
                    type="number"
                    label="KM au compteur, à la fin de la course"
                    placholder="KM au compteur"
                    :disabled="!!action.executed_at"
                    v-model="action.mileage_end" />
                </b-col>
              </b-row>

              <hr>

              <b-row>
                <b-col lg="6" v-if="!action.executed_at">
                  <p>Envoyez une photo de vos dépenses.</p>

                  <forms-image-uploader
                    label="Photo des dépenses"
                    field="expense"
                    v-model="action.expense" />

                  <p><small>
                    Cette photo doit afficher le détails des dépenses.
                  </small></p>
                </b-col>
                <b-col lg="6" v-else-if="action.expense">
                  <a href="#" v-b-modal="'handover-collective-expense'">
                    <img :src="action.expense ? action.expense.sizes.thumbnail : ''">
                  </a>

                  <b-modal size="xl"
                    title="Photo des dépenses"
                    :id="'handover-collective-expense'" footer-class="d-none">
                    <img class="img-fit" :src="action.expense.url">
                  </b-modal>
                </b-col>

                <b-col lg="6">
                  <forms-validated-input
                    id="purchases_amount" name="purchases_amount"
                    type="number" :min="0" :step="0.01"
                    label="Total des dépenses"
                    placholder="Total des dépenses"
                    :disabled="!!action.executed_at"
                    v-model="action.purchases_amount" />
                </b-col>
              </b-row>

              <hr>

              <b-row>
                <b-col>
                  <forms-validated-input
                    v-if="userRole === 'borrower' && !action.executed_at"
                    id="comments_by_borrower" name="comments_by_borrower"
                    type="textarea" :rows="3" :disabled="!!action.commented_by_borrower_at"
                    label="Laissez un message au propriétaire (facultatif)"
                    placeholder="Commentaire sur la réservation"
                    v-model="action.comments_by_borrower" />
                  <blockquote v-else-if="action.comments_by_borrower">
                    {{ action.comments_by_borrower }}
                    <div class="user-avatar" :style="{ backgroundImage: borrowerAvatar }" />
                  </blockquote>
                </b-col>
              </b-row>

              <b-row>
                <b-col>
                  <forms-validated-input v-if="userRole === 'owner' && !action.executed_at"
                    id="comments_by_owner" name="comments_by_owner"
                    type="textarea" :rows="3" :disabled="!!action.commented_by_owner_at"
                    label="Laissez un message à l'emprunteur (facultatif)"
                    placeholder="Commentaire sur la réservation"
                    v-model="action.comments_by_owner" />
                  <blockquote v-else-if="action.comments_by_owner">
                    {{ action.comments_by_owner }}
                    <div class="user-avatar" :style="{ backgroundImage: ownerAvatar }" />
                  </blockquote>
                </b-col>
              </b-row>

              <b-row class="loan-actions-handover-collective__buttons text-center"
                v-if="!action.executed_at">
                <b-col>
                  <b-button type="submit" size="sm" variant="success" class="mr-3">
                    Enregistrer
                  </b-button>
                </b-col>
              </b-row>
            </b-form>
          </validation-observer>
        </div>

        <div v-else-if="loan.loanable.has_padlock">
          <p>
            Le cadenas du véhicule sera automatiquement dissocié de application NOKE
            lorsque vous aurez complété la remise du véhicule.
          </p>

          <p>
            Si vous prévoyez avoir du retard, ajoutez une extension: d'autres membres
            LocoMotion attendent peut-être ce véhicule.
          </p>

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
                  <a href="#" v-b-modal="'handover-collective-image'">
                    <img :src="action.image ? action.image.sizes.thumbnail : ''">
                  </a>

                  <b-modal size="xl"
                    title="Photo de l'état du véhicule"
                    :id="'handover-collective-image'" footer-class="d-none">
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
            </b-form>
          </validation-observer>
        </div>

        <div v-else>
          <b-row v-if="!action.executed_at">
            <b-col>
              <p v-if="userRole === 'borrower'">
                Rendez le véhicule au propriétaire.
              </p>
              <p v-else>
                L'emprunteur vous contactera pour arranger la remise du véhicule.
              </p>
            </b-col>
          </b-row>
          <b-row v-else>
            <b-col>
              <p v-if="action.status !== 'canceled'">
                La remise du véhicule a été effectuée.
              </p>
              <p v-else>
                La remise du véhicule a été annulée.
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
        </div>

        <div v-if="isContestable" >
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

          <b-row class="loan-actions-handover-collective__buttons text-center">
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
  name: 'LoanActionsHandoverCollective',
  mixins: [LoanActionsMixin],
  components: {
    FormsImageUploader,
    FormsValidatedInput,
  },
};
</script>

<style lang="scss">
</style>
