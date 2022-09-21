<template>
  <b-card no-body class="loan-form loan-actions loan-actions-takeover">
    <b-card-header
      header-tag="header"
      role="tab"
      class="loan-actions__header"
      v-b-toggle.loan-actions-takeover
    >
      <h2>
        <svg-danger
          v-if="(action.status === 'in_process' && loanIsCanceled) || action.status === 'canceled'"
        />
        <svg-waiting v-else-if="action.status === 'in_process'" />
        <svg-check v-else-if="action.status === 'completed'" />

        Informations avant de partir
      </h2>

      <span v-if="item.is_contested && action.status === 'in_process' && !loanIsCanceled">
        Bloqué
      </span>
      <span v-else>
        <!--
          Canceled loans: current step remains in-process.
          Canceled action means contestation. Give way to canceled-loan status.
        -->
        <span
          v-if="(action.status === 'in_process' || action.status === 'canceled') && loanIsCanceled"
        >
          Emprunt annulé &bull; {{ item.canceled_at | datetime }}
        </span>
        <span v-else-if="action.status == 'in_process'"> En attente </span>
        <span v-else-if="action.status === 'completed'">
          Complété &bull; {{ action.executed_at | datetime }}
        </span>
        <span v-else-if="action.status === 'canceled'">
          Contesté &bull; {{ action.executed_at | datetime }}
        </span>
      </span>
    </b-card-header>

    <b-card-body>
      <b-collapse
        id="loan-actions-takeover"
        role="tabpanel"
        accordion="loan-actions"
        :visible="open"
      >
        <div
          v-if="(action.status === 'in_process' || action.status === 'canceled') && loanIsCanceled"
        >
          <p>L'emprunt a été annulé. Cette étape ne peut pas être complétée.</p>
        </div>
        <div v-else-if="item.loanable.type === 'car'">
          <validation-observer ref="observer" v-slot="{ passes }">
            <b-alert show variant="warning" v-if="item.loanable.instructions">
              <div class="alert-heading">
                <h4>Instructions du propriétaire pour l'utilisation du véhicule</h4>
              </div>
              <div class="owner-instructions-text">
                <p>{{ item.loanable.instructions }}</p>
              </div>
            </b-alert>

            <!-- Add message if user is borrower, but not owner. -->
            <b-row v-if="userRoles.includes('borrower') && !userRoles.includes('owner')">
              <b-col>
                <p>
                  Avez-vous bien pris connaissance de l'état de cette auto?<br />
                  Il est important de le faire avant de prendre en prendre possession. Ainsi, s'il
                  arrive un pépin, vous allez être en mesure d'en discuter avec la personne
                  propriétaire de l'auto.
                </p>
              </b-col>
            </b-row>

            <b-form
              :novalidate="true"
              class="loan-actions-takeover__form"
              @submit.stop.prevent="passes(completeAction)"
            >
              <b-row>
                <b-col
                  lg="6"
                  v-if="(!action.executed_at && !loanIsCanceled) || userIsAdmin"
                  class="loan-actions-takeover__form__image"
                >
                  <p>Envoyez une photo du tableau de bord.</p>

                  <forms-image-uploader
                    label="Photo du tableau de bord"
                    field="image"
                    v-model="action.image"
                  />

                  <p>
                    <small>
                      On vous demande une preuve? Prenez une photo du tableau de bord de l'auto pour
                      rentrer les bonnes informations (kilométrage, essence). Cette photo est
                      facultative.
                    </small>
                  </p>
                </b-col>
                <b-col lg="6" v-else-if="action.image" class="loan-actions-takeover__form__image">
                  <a href="#" v-b-modal="'takeover-image'">
                    <img :src="action.image ? action.image.sizes.thumbnail : ''" />
                  </a>

                  <b-modal
                    size="xl"
                    title="Photo du tableau de bord"
                    :id="'takeover-image'"
                    footer-class="d-none"
                  >
                    <img class="img-fit" :src="action.image.url" />
                  </b-modal>
                </b-col>

                <b-col lg="6">
                  <forms-validated-input
                    id="mileage_beginning"
                    name="mileage_beginning"
                    type="number"
                    :rules="{ required: true }"
                    label="KM au compteur, au début de la course"
                    placeholder="KM au compteur"
                    :disabled="(!!action.executed_at || loanIsCanceled) && !userIsAdmin"
                    v-model="action.mileage_beginning"
                  />
                </b-col>
              </b-row>

              <b-row
                class="loan-actions-takeover__buttons text-center"
                v-if="(!action.executed_at && !loanIsCanceled && !item.is_contested) || userIsAdmin"
              >
                <b-col>
                  <b-button type="submit" size="sm" variant="success" class="mr-3">
                    <span v-if="isContested">Corriger</span>
                    <span v-else>Enregistrer</span>
                  </b-button>
                </b-col>
              </b-row>

              <b-row class="loan-actions__alert" v-if="!action.executed_at && !loanIsCanceled">
                <b-col>
                  <b-alert variant="warning" show>
                    Les informations de l'emprunt peuvent être modifiées jusqu'à 48h après sa
                    conclusion. À partir de ce moment, le coût de l'emprunt sera validé avec les
                    détails ci-dessus.
                  </b-alert>
                </b-col>
              </b-row>
            </b-form>
          </validation-observer>
        </div>

        <div v-else-if="item.loanable.has_padlock">
          <!-- Loanable is not a car and has a padlock. -->
          <b-alert show variant="warning" v-if="item.loanable.instructions">
            <div class="alert-heading">
              <h4>Instructions du propriétaire pour l'utilisation du véhicule</h4>
            </div>
            <div class="owner-instructions-text">
              <p>{{ item.loanable.instructions }}</p>
            </div>
          </b-alert>

          <b-alert show variant="info">
            <p>
              Le cadenas du véhicule sera automatiquement associé à votre application NOKE à temps
              pour la prise de possession.
            </p>
            <p>Vous avez un problème avec le cadenas?</p>
            <p>
              Contactez-nous entre 9h et 20h au 438-476-3343<br />
              (cette ligne est dédiée uniquement aux problèmes liés aux cadenas)
            </p>
          </b-alert>

          <validation-observer ref="observer" v-slot="{ passes }">
            <b-form
              :novalidate="true"
              class="register-form__form"
              @submit.stop.prevent="passes(completeAction)"
            >
              <b-row v-if="!action.executed_at">
                <b-col>
                  <p>Envoyez une photo de l'état du véhicule.</p>

                  <forms-image-uploader
                    label="Photo du véhicule"
                    field="image"
                    v-model="action.image"
                  />

                  <p>
                    <small>
                      Cette photo est optionnelle mais permet à LocoMotion de déterminer à quel
                      moment un bris s'est produit, le cas échéant.
                    </small>
                  </p>
                </b-col>
              </b-row>
              <b-row v-else-if="action.image">
                <b-col>
                  <a href="#" v-b-modal="'takeover-image'">
                    <img :src="action.image ? action.image.sizes.thumbnail : ''" />
                  </a>

                  <b-modal
                    size="xl"
                    title="Photo de l'état du véhicule"
                    :id="'takeover-image'"
                    footer-class="d-none"
                  >
                    <img class="img-fit" :src="action.image.url" />
                  </b-modal>
                </b-col>
              </b-row>

              <b-row
                class="loan-actions-takeover__buttons text-center"
                v-if="(!action.executed_at && !item.is_contested) || userIsAdmin"
              >
                <b-col>
                  <b-button type="submit" size="sm" variant="success" class="mr-3">
                    <span v-if="isContested">Corriger</span>
                    <span v-else>Enregistrer</span>
                  </b-button>
                </b-col>
              </b-row>

              <b-row class="loan-actions__alert" v-if="!action.executed_at">
                <b-col>
                  <b-alert variant="warning" show>
                    Les informations de l'emprunt peuvent être modifiées jusqu'à 48h après sa
                    conclusion. À partir de ce moment, le coût de l'emprunt sera validé avec les
                    détails ci-dessus.
                  </b-alert>
                </b-col>
              </b-row>
            </b-form>
          </validation-observer>
        </div>

        <div v-else>
          <!-- Loanable is not a car and it does not have a padlock. -->
          <b-alert show variant="warning" v-if="item.loanable.instructions">
            <div class="alert-heading">
              <h4>Instructions du propriétaire pour l'utilisation du véhicule</h4>
            </div>
            <div class="owner-instructions-text">
              <p>{{ item.loanable.instructions }}</p>
            </div>
          </b-alert>
          <b-row v-if="!action.executed_at">
            <b-col>
              <p v-if="userRoles.includes('borrower') && !userRoles.includes('owner')">
                Demandez au propriétaire de récupérer le véhicule.
              </p>
              <p v-if="!userRoles.includes('borrower') && userRoles.includes('owner')">
                L'emprunteur vous contactera pour arranger la prise de possession du véhicule.
              </p>
            </b-col>
          </b-row>
          <b-row v-else>
            <b-col>
              <p v-if="action.status !== 'canceled'">La prise de possession a été effectuée.</p>
              <p v-else>La prise de possession a été annulée.</p>
            </b-col>
          </b-row>

          <b-row class="loan-actions-takeover__buttons text-center" v-if="!action.executed_at">
            <b-col>
              <b-button
                type="submit"
                size="sm"
                variant="success"
                class="mr-3"
                :disabled="actionLoading"
                @click="completeAction"
              >
                C'est fait!
              </b-button>
            </b-col>
          </b-row>

          <b-row class="loan-actions__alert" v-if="!action.executed_at">
            <b-col>
              <b-alert variant="warning" show>
                Les informations de l'emprunt peuvent être modifiées jusqu'à 48h après sa
                conclusion. À partir de ce moment, le coût de l'emprunt sera validé avec les détails
                ci-dessus.
              </b-alert>
            </b-col>
          </b-row>
        </div>

        <div v-if="!isContested">
          <div v-if="isContestable && !loanIsCanceled">
            <hr />

            <validation-observer ref="observer" v-slot="{ passes }">
              <b-form
                :novalidate="true"
                class="loan-actions-takeover__contestation"
                @submit.stop.prevent="passes(cancelAction)"
              >
                <b-row>
                  <b-col lg="6">
                    <p>Cette information est-elle incorrecte?</p>
                    <p>
                      Pour la modifier, vous pouvez procéder à une "contestation". Par cette
                      procédure, un membre de l'équipe LocoMotion sera appelé à arbitrer la
                      résolution du conflit entre l'emprunteur et le propriétaire.
                    </p>
                  </b-col>

                  <b-col lg="6">
                    <forms-validated-input
                      id="comments_on_contestation"
                      name="comments_on_contestation"
                      :rules="{ required: true }"
                      type="textarea"
                      :rows="3"
                      label="Commentaires sur la contestation"
                      placeholder="Commentaire sur la contestation"
                      v-model="action.comments_on_contestation"
                    />
                  </b-col>
                </b-row>

                <b-row class="loan-actions-takeover__buttons text-center">
                  <b-col>
                    <b-button size="sm" variant="outline-danger" type="submit">
                      Contester
                    </b-button>
                  </b-col>
                </b-row>
              </b-form>
            </validation-observer>
          </div>
        </div>
        <div v-else-if="!loanIsCanceled">
          <hr />

          <p>Les données ont été contestées {{ action.comments_on_contestation ? `:` : `` }}</p>

          <b-alert variant="warning" v-if="action.comments_on_contestation" show>
            {{ action.comments_on_contestation }}
          </b-alert>

          <p>
            Un membre de l'équipe LocoMotion contactera les participant-e-s et ajustera les données.
          </p>
        </div>
      </b-collapse>
    </b-card-body>
  </b-card>
</template>

<script>
import FormsImageUploader from "@/components/Forms/ImageUploader.vue";
import FormsValidatedInput from "@/components/Forms/ValidatedInput.vue";

import LoanActionsMixin from "@/mixins/LoanActionsMixin";

export default {
  name: "LoanActionsTakeover",
  mixins: [LoanActionsMixin],
  components: {
    FormsImageUploader,
    FormsValidatedInput,
  },
  mounted() {
    if (!this.action.mileage_beginning) {
      this.action.mileage_beginning = 0;
    }
  },
};
</script>

<style lang="scss">
.loan-actions-takeover {
  &__form {
    &__image a {
      display: inline-block;
      margin-bottom: 1rem;
    }
  }
}
.owner-instructions-text {
  white-space: pre;
}
</style>
