<template>
  <b-card no-body class="loan-form loan-actions loan-actions-handover">
    <b-card-header
      header-tag="header"
      role="tab"
      class="loan-actions__header"
      v-b-toggle.loan-actions-handover
    >
      <h2>
        <svg-danger
          v-if="
            (action.status === 'in_process' && loanIsCanceled) ||
            action.status === 'canceled' ||
            hasActiveExtensions
          "
        />
        <svg-waiting v-else-if="action.status === 'in_process'" />
        <svg-check v-else-if="action.status === 'completed'" />

        Retour
      </h2>

      <span
        v-if="
          hasActiveExtensions ||
          (item.is_contested && action.status === 'in_process' && !loanIsCanceled)
        "
      >
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
        id="loan-actions-handover"
        role="tabpanel"
        accordion="loan-actions"
        :visible="open"
      >
        <div
          v-if="(action.status === 'in_process' || action.status === 'canceled') && loanIsCanceled"
        >
          <p>L'emprunt a été annulé. Cette étape ne peut pas être complétée.</p>
        </div>
        <div v-else-if="hasActiveExtensions">
          <p>
            Une demande d'extension est en cours. Elle doit être complétée (acceptée, refusée ou
            annulée) avant de poursuivre.
          </p>
        </div>
        <div v-else-if="item.loanable.type === 'car'">
          <validation-observer ref="observer" v-slot="{ passes }">
            <b-form
              :novalidate="true"
              class="loan-actions-handover__form"
              @submit.stop.prevent="passes(completeAction)"
            >
              <b-row>
                <b-col
                  lg="6"
                  v-if="(!action.executed_at && !loanIsCanceled) || userIsAdmin"
                  class="loan-actions-handover__form__image"
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
                <b-col lg="6" v-else-if="action.image" class="loan-actions-handover__form__image">
                  <a href="#" v-b-modal="'handover-image'">
                    <img :src="action.image ? action.image.sizes.thumbnail : ''" />
                  </a>

                  <b-modal
                    size="xl"
                    title="Photo du tableau de bord"
                    :id="'handover-image'"
                    footer-class="d-none"
                  >
                    <img class="img-fit" :src="action.image.url" />
                  </b-modal>
                </b-col>

                <b-col lg="6">
                  <forms-validated-input
                    id="mileage_end"
                    name="mileage_end"
                    type="number"
                    label="KM au compteur, à la fin de la course"
                    :rules="{ min_value: mileageBeginning }"
                    placholder="KM au compteur"
                    :disabled="(!!action.executed_at || loanIsCanceled) && !userIsAdmin"
                    v-model="action.mileage_end"
                  />
                </b-col>
              </b-row>

              <hr />

              <b-row>
                <b-col lg="6" v-if="(!action.executed_at && !loanIsCanceled) || userIsAdmin">
                  <p>Envoyez une photo de vos dépenses.</p>

                  <forms-image-uploader
                    label="Photo des dépenses"
                    field="expense"
                    v-model="action.expense"
                  />

                  <p><small> Cette photo doit afficher le détails des dépenses. </small></p>
                </b-col>
                <b-col lg="6" v-else-if="action.expense">
                  <a href="#" v-b-modal="'handover-expense'">
                    <img :src="action.expense ? action.expense.sizes.thumbnail : ''" />
                  </a>

                  <b-modal
                    size="xl"
                    title="Photo des dépenses"
                    :id="'handover-expense'"
                    footer-class="d-none"
                  >
                    <img class="img-fit" :src="action.expense.url" />
                  </b-modal>
                </b-col>

                <b-col lg="6">
                  <forms-validated-input
                    id="purchases_amount"
                    name="purchases_amount"
                    type="number"
                    :min="0"
                    :step="0.01"
                    label="Total des dépenses"
                    placholder="Total des dépenses"
                    :disabled="(!!action.executed_at || loanIsCanceled) && !userIsAdmin"
                    v-model="action.purchases_amount"
                  />
                </b-col>
              </b-row>

              <!--
                Display rule if actions not completed and not owner and borrower at the
                same time or if message exists.
              -->
              <hr
                v-if="
                  (!action.executed_at &&
                    !loanIsCanceled &&
                    !(userRoles.includes('borrower') && userRoles.includes('owner'))) ||
                  action.comments_by_borrower ||
                  action.comments_by_owner
                "
              />

              <b-row>
                <b-col>
                  <!-- Allow inputing a message to the owner if user is not the owner. -->
                  <forms-validated-input
                    v-if="
                      !action.executed_at &&
                      !loanIsCanceled &&
                      userRoles.includes('borrower') &&
                      !userRoles.includes('owner')
                    "
                    id="comments_by_borrower"
                    name="comments_by_borrower"
                    type="textarea"
                    :rows="3"
                    :disabled="!!action.commented_by_borrower_at"
                    label="Laissez un message au propriétaire (facultatif)"
                    placeholder="Commentaire sur la réservation"
                    v-model="action.comments_by_borrower"
                  />
                  <blockquote v-else-if="action.comments_by_borrower">
                    {{ action.comments_by_borrower }}
                    <user-avatar :user="borrower.user" />
                  </blockquote>
                </b-col>
              </b-row>

              <b-row>
                <b-col>
                  <!-- Allow inputing a message to the borrower if user is not the borrower. -->
                  <forms-validated-input
                    v-if="
                      !action.executed_at &&
                      !loanIsCanceled &&
                      userRoles.includes('owner') &&
                      !userRoles.includes('borrower')
                    "
                    id="comments_by_owner"
                    name="comments_by_owner"
                    type="textarea"
                    :rows="3"
                    :disabled="!!action.commented_by_owner_at"
                    label="Laissez un message à l'emprunteur (facultatif)"
                    placeholder="Commentaire sur la réservation"
                    v-model="action.comments_by_owner"
                  />
                  <blockquote v-else-if="action.comments_by_owner">
                    {{ action.comments_by_owner }}
                    <user-avatar :user="owner.user" />
                  </blockquote>
                </b-col>
              </b-row>

              <b-row
                class="loan-actions-handover__buttons text-center"
                v-if="!action.executed_at && !loanIsCanceled && !item.is_contested"
              >
                <b-col>
                  <b-button
                    type="submit"
                    size="sm"
                    variant="success"
                    class="mr-3"
                    :disabled="!isContested && startsInTheFuture"
                  >
                    <span v-if="isContested">Corriger</span>
                    <span v-else>Enregistrer</span>
                  </b-button>
                  <div v-if="!isContested && startsInTheFuture">
                    <small class="text-muted">
                      Il sera possible de terminer l'emprunt après l'heure du début de la
                      réservation ({{ item.departure_at | shortDate }} à
                      {{ item.departure_at | time }}).
                    </small>
                  </div>
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
          <p>
            Le cadenas du véhicule sera automatiquement dissocié de application NOKE lorsque vous
            aurez complété le retour du véhicule.
          </p>

          <b-alert show variant="info">
            <p>Vous avez un problème avec le cadenas?</p>
            <p>
              Contactez-nous entre 9h et 20h au 438-476-3343<br />
              (cette ligne est dédiée uniquement aux problèmes liés aux cadenas)
            </p>
          </b-alert>

          <p>
            Si vous prévoyez avoir du retard, ajoutez une extension: d'autres membres LocoMotion
            attendent peut-être ce véhicule.
          </p>

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
                  <a href="#" v-b-modal="'handover-image'">
                    <img :src="action.image ? action.image.sizes.thumbnail : ''" />
                  </a>

                  <b-modal
                    size="xl"
                    title="Photo de l'état du véhicule"
                    :id="'handover-image'"
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
                  <b-button
                    type="submit"
                    size="sm"
                    variant="success"
                    class="mr-3"
                    :disabled="!isContested && startsInTheFuture"
                  >
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
          <b-row v-if="!action.executed_at">
            <b-col>
              <p v-if="userRoles.includes('borrower') && !userRoles.includes('owner')">
                Rendez le véhicule au propriétaire.
              </p>
              <p v-if="!userRoles.includes('borrower') && userRoles.includes('owner')">
                L'emprunteur vous contactera pour arranger le retour du véhicule.
              </p>
            </b-col>
          </b-row>
          <b-row v-else>
            <b-col>
              <p v-if="action.status !== 'canceled'">Le retour du véhicule a été effectué.</p>
              <p v-else>Le retour du véhicule a été annulé.</p>
            </b-col>
          </b-row>

          <b-row class="loan-actions-takeover__buttons text-center" v-if="!action.executed_at">
            <b-col>
              <b-button
                type="submit"
                size="sm"
                variant="success"
                class="mr-3"
                :disabled="actionLoading || startsInTheFuture"
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

            <b-row>
              <b-col lg="6">
                <p>Cette information est-elle incorrecte?</p>
                <p>
                  Pour la modifier, vous pouvez procéder à une "contestation". Par cette procédure,
                  un membre de l'équipe LocoMotion sera appelé à arbitrer la résolution du conflit
                  entre l'emprunteur et le propriétaire.
                </p>
              </b-col>

              <b-col lg="6">
                <forms-validated-input
                  id="comments_on_contestation"
                  name="comments_on_contestation"
                  type="textarea"
                  :rows="3"
                  label="Commentaires sur la contestation"
                  placeholder="Commentaire sur la contestation"
                  v-model="action.comments_on_contestation"
                />
              </b-col>
            </b-row>

            <b-row class="loan-actions-handover__buttons text-center">
              <b-col>
                <b-button
                  size="sm"
                  variant="outline-danger"
                  :disabled="actionLoading"
                  @click="cancelAction"
                >
                  Contester
                </b-button>
              </b-col>
            </b-row>
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

          <div v-if="userIsAdmin" class="text-center">
            <b-button
              type="submit"
              size="sm"
              variant="success"
              class="mr-3"
              :disabled="actionLoading"
              @click="completeAction"
            >
              Résoudre la contestation
            </b-button>
          </div>
        </div>
      </b-collapse>
    </b-card-body>
  </b-card>
</template>

<script>
import FormsImageUploader from "@/components/Forms/ImageUploader.vue";
import FormsValidatedInput from "@/components/Forms/ValidatedInput.vue";
import UserAvatar from "@/components/User/Avatar.vue";

import LoanActionsMixin from "@/mixins/LoanActionsMixin";
import LoanStepsSequence from "@/mixins/LoanStepsSequence";

export default {
  name: "LoanActionsHandover",
  mixins: [LoanActionsMixin, LoanStepsSequence],
  mounted() {
    if (!this.action.mileage_end) {
      this.action.mileage_end = this.item.estimated_distance + this.mileageBeginning;
    }
    if (!this.action.purchases_amount) {
      this.action.purchases_amount = 0;
    }
  },
  computed: {
    mileageBeginning() {
      return this.item.actions.find((a) => a.type === "takeover").mileage_beginning;
    },
    startsInTheFuture() {
      return this.$second.isBefore(this.item.departure_at, "minute");
    },
  },
  watch: {
    action(newValue) {
      if (!newValue.mileage_end) {
        this.action.mileage_end = this.item.estimated_distance + this.mileageBeginning;
      }
      if (!newValue.purchases_amount) {
        this.action.purchases_amount = 0;
      }
    },
  },

  components: {
    FormsImageUploader,
    FormsValidatedInput,
    UserAvatar,
  },
};
</script>

<style lang="scss">
.loan-actions-handover {
  &__form {
    &__image a {
      display: inline-block;
      margin-bottom: 1rem;
    }
  }
}
</style>
