<template>
  <b-card no-body class="loan-form loan-actions loan-actions-extension"
    :id="`loan-extension-${action.id || 'new'}`">
    <b-card-header header-tag="header" role="tab" class="loan-actions__header"
      v-b-toggle="`loan-actions-extension-${action.id || 'new'}`">
      <b-row>
        <b-col>
          <h2>
            <svg-waiting v-if="action.status === 'in_process' && !item.canceled_at" />
            <svg-check v-else-if="action.status === 'completed'" />
            <svg-danger v-else-if="action.status === 'canceled' || item.canceled_at" />

            Retard
          </h2>

          <span v-if="action.status == 'in_process' & !item.canceled_at">En attente</span>
          <span v-else-if="action.status === 'completed'">
            Validé &bull; {{ action.executed_at | datetime }}
          </span>
          <span v-else-if="action.status === 'canceled' || item.canceled_at">
            Contesté &bull; {{ action.executed_at || item.canceled_at | datetime }}
          </span>
        </b-col>

        <b-col lg="8" v-if="item.status !== 'completed'">
          <loan-next-date :loanable-id="item.loanable.id" :loan-id="item.id" />
        </b-col>
      </b-row>
    </b-card-header>


    <b-card-body>
      <b-collapse :id="`loan-actions-extension-${action.id || 'new'}`"
        role="tabpanel" accordion="loan-actions"
        :visible="open">
        <div v-if="!!action.executed_at">
          <p>Demande d'extension jusqu'au {{ returnAt | datetime }}.</p>

          <blockquote v-if="action.comments_on_extension">
            {{ action.comments_on_extension }}
            <div class="user-avatar" :style="{ backgroundImage: borrowerAvatar }" />
          </blockquote>

          <blockquote v-if="action.message_for_borrower">
            {{ action.message_for_borrower }}
            <div class="user-avatar" :style="{ backgroundImage: ownerAvatar }" />
          </blockquote>
        </div>

        <div v-else-if="userRoles.includes('borrower')">
          <div v-if="!action.id">
            <p>
              Indiquez une nouvelle heure de retour et laissez un message.
            </p>

            <validation-observer ref="observer" v-slot="{ passes }">
              <b-form :novalidate="true" class="form loan-actions-extension__form"
                @submit.stop.prevent="passes(createAction)"
                @reset.stop.prevent="$emit('reset')">
                <b-row>
                  <b-col lg="6">
                    <forms-validated-input name="return_at"
                      :rules="{ required: true }"
                      label="Nouvelle date de retour"
                      type="datetime"
                      :disabled-dates="disabledDates" :disabled-times="disabledTimes"
                      v-model="returnAt" />
                  </b-col>
                  <b-col>
                    <forms-validated-input name="comments_on_extension"
                      :rules="{ required: true }"
                      label="Commentaire" type="textarea" :rows="3"
                      v-model="action.comments_on_extension" />
                  </b-col>
                </b-row>

                <div class="loan-actions-extension__buttons text-center">
                  <b-button size="sm" type="submit" variant="success" class="mr-3">
                    Créer
                  </b-button>

                  <b-button size="sm" variant="outline-warning" @click="abortAction">
                    Annuler
                  </b-button>
                </div>
              </b-form>
            </validation-observer>
          </div>

          <div v-else-if="!action.executed_at" class="text-center">
            <p>Demande d'extension jusqu'au {{ returnAt | datetime }}.</p>
            <p>
              Merci d'avoir enregistré votre demande d'extension sur la
              plateforme! Maintenant, contactez votre voisin-e pour voir
              directement avec lui/elle si son véhicule est disponible.
            </p>
            <p v-if="item.loanable.owner">{{ item.loanable.owner.user.phone | phone }}</p>
          </div>
        </div>

        <div v-else>
          <p>
            {{ borrower.user.name }} demande une extension jusqu'au
            {{ returnAt | datetime }}.
          </p>

          <blockquote v-if="action.comments_on_extension">
            {{ action.comments_on_extension }}
            <div class="user-avatar" :style="{ backgroundImage: borrowerAvatar }" />
          </blockquote>

          <div class="loan-actions-extension__message_for_borrower text-center mb-3">
            <forms-validated-input type="textarea" name="message_for_borrower"
              v-model="action.message_for_borrower"
              label="Laissez un message à l'emprunteur (facultatif)" />
          </div>

          <div class="loan-actions-extension__buttons text-center">
            <b-button size="sm" variant="success" class="mr-3" @click="completeAction">
              Accepter
            </b-button>

            <b-button size="sm" variant="outline-danger" @click="cancelAction">
              Refuser
            </b-button>
          </div>

          <div class="loan-actions__alert">
            <b-alert variant="warning" show>
              Dans 48h, vous ne pourrez plus modifier vos informations.
              Nous validerons le coût de l'emprunt avec les détails ci-dessus.
            </b-alert>
          </div>
        </div>
      </b-collapse>
    </b-card-body>
  </b-card>
</template>

<script>
import FormsValidatedInput from '@/components/Forms/ValidatedInput.vue';

import LoanNextDate from '@/components/Loan/NextDate.vue';

import LoanFormMixin from '@/mixins/LoanFormMixin';
import LoanActionsMixin from '@/mixins/LoanActionsMixin';

const { computed: { disabledDates, disabledTimes } } = LoanFormMixin;

export default {
  name: 'LoanActionsExtension',
  mixins: [LoanActionsMixin],
  components: {
    FormsValidatedInput,
    LoanNextDate,
  },
  computed: {
    disabledDates,
    disabledTimes,
    returnAt: {
      get() {
        return this.$dayjs(this.item.departure_at)
          .add(this.action.new_duration, 'minute')
          .format('YYYY-MM-DD HH:mm:ss');
      },
      set(val) {
        this.action.new_duration = this.$dayjs(val)
          .diff(this.$dayjs(this.item.departure_at), 'minute');
      },
    },
  },
};
</script>

<style lang="scss">
</style>
