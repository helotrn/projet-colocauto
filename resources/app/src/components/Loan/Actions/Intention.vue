<template>
  <b-card no-body class="loan-form loan-actions loan-actions-intention">
    <b-card-header header-tag="header" role="tab" class="loan-actions__header">
      <h2 v-b-toggle.loan-actions-intention>
        <svg-waiting v-if="action.status === 'in_process'" />
        <svg-check v-else-if="action.status === 'completed'" />
        <svg-danger v-else-if="action.status === 'canceled'" />

        Confirmation de l'emprunt
      </h2>

      <span v-if="action.status == 'in_process'">En attente d'approbation</span>
      <span v-else-if="action.status === 'completed'">
        Approuvé &bull; {{ action.executed_at | datetime }}
      </span>
      <span v-else-if="action.status === 'canceled'">
        Refusé &bull; {{ action.executed_at | datetime }}
      </span>
    </b-card-header>


    <b-card-body>
      <b-collapse id="loan-actions-intention" role="tabpanel" accordion="loan-actions"
        :visible="open">
        <div class="loan-actions-intention__image mb-3 text-center">
          <div :style="{ backgroundImage: borrowerAvatar }" />
        </div>

        <div class="loan-actions-intention__description text-center mb-3">
          <p v-if="userRole === 'owner'">
            {{ borrower.user.name }} veut vous emprunter {{ loanablePrettyName }}.
          </p>
          <p v-else-if="item.loanable.owner">
            Vous avez demandé à {{ item.loanable.owner.user.name }} de lui
            emprunter {{ loanablePrettyName }}.
          </p>

          <label>Raison de l'emprunt</label>
          <p>{{ item.reason }}</p>

          <blockquote v-if="item.message_for_owner">
            {{ item.message_for_owner }}
            <div class="user-avatar" :style="{ backgroundImage: borrowerAvatar }" />
          </blockquote>

          <blockquote v-if="action.message_for_borrower
            && (userRole !== 'owner' || !!action.executed_at)">
            {{ action.message_for_borrower }}
            <div class="user-avatar" :style="{ backgroundImage: ownerAvatar }" />
          </blockquote>
        </div>

        <div v-if="!action.executed_at">
          <div class="loan-actions-intention__see-details text-center mb-3">
            <b-button size="sm" variant="outline-info" v-b-toggle.loan-actions-new>
              Voir les détails
            </b-button>
          </div>

          <div v-if="userRole === 'owner'">
            <div class="loan-actions-intention__message_for_borrower text-center mb-3">
              <forms-validated-input type="textarea" name="message_for_borrower"
                v-model="action.message_for_borrower"
                label="Laissez un message à l'emprunteur (facultatif)" />
            </div>

            <div class="loan-actions-intention__buttons text-center">
              <b-button size="sm" variant="success" class="mr-3" @click="completeAction">
                Accepter
              </b-button>

              <b-button size="sm" variant="outline-danger" @click="cancelAction">
                Refuser
              </b-button>
            </div>
          </div>
          <div v-else-if="item.loanable.owner" class="text-center">
            <p>Contactez le propriétaire pour qu'il confirme votre demande.</p>
            <p>{{ item.loanable.owner.user.phone }}</p>
          </div>
        </div>

        <div class="loan-actions__alert" v-if="item.loanable.type === 'car'">
          <b-alert variant="warning" show>
            <p>
              Desjardins assurances ne couvrera le trajet que s'il est bien renseigné sur
              LocoMotion! Pensez à accepter et vérifier le pré-paiement de la réservation ici.
            </p>

            <p>
              <router-link to="/assurances-desjardins">
                Voir les conditions d'assurances
              </router-link>
            </p>
          </b-alert>
        </div>
      </b-collapse>
    </b-card-body>
  </b-card>
</template>

<script>
import FormsValidatedInput from '@/components/Forms/ValidatedInput.vue';

import LoanActionsMixin from '@/mixins/LoanActionsMixin';

export default {
  name: 'LoanActionsIntention',
  mixins: [LoanActionsMixin],
  components: {
    FormsValidatedInput,
  },
  computed: {
    loanablePrettyName() {
      let article;
      let type;

      switch (this.item.loanable.type) {
        case 'car':
          article = 'sa';
          type = 'voiture';
          break;
        case 'bike':
          article = 'son';
          type = 'vélo';
          break;
        case 'trailer':
          article = 'sa';
          type = 'remorque';
          break;
        default:
          article = 'son';
          type = 'objet';
          break;
      }

      if (this.userRole === 'owner') {
        article = 'votre';
      }

      return `${article} ${type}`;
    },
  },
};
</script>

<style lang="scss">
.loan-actions-intention {
  &__image {
    > div {
      margin: 0 auto;
      height: 85px;
      width: 85px;
      background-size: cover;
      background-repeat: no-repeat;
      background-position: center center;
      border-radius: 100%;
    }
  }
}
</style>
