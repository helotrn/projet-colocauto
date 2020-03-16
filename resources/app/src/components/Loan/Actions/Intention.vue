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
            {{ loan.borrower.user.name }} veut vous emprunter {{ loanablePrettyName }}.
          </p>
          <p v-else>
            Vous avez demandé à {{ loan.borrower.user.name }} de lui
            emprunter {{ loanablePrettyName }}.
          </p>

          <label>Raison de l'emprunt</label>
          <p>{{ loan.reason }}</p>

          <blockquote v-if="loan.message_for_owner">
            {{ loan.message_for_owner }}
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
          <div v-else class="text-center">
            <p>Contactez le propriétaire pour qu'il confirme votre demande.</p>
            <p>{{ loan.loanable.owner.user.phone }}</p>
          </div>
        </div>
      </b-collapse>
    </b-card-body>
  </b-card>
</template>

<script>
import FormsValidatedInput from '@/components/Forms/ValidatedInput.vue';

import Check from '@/assets/svg/check.svg';
import Danger from '@/assets/svg/danger.svg';
import Waiting from '@/assets/svg/waiting.svg';

export default {
  name: 'LoanActionsIntention',
  components: {
    FormsValidatedInput,
    'svg-check': Check,
    'svg-danger': Danger,
    'svg-waiting': Waiting,
  },
  props: {
    action: {
      type: Object,
      required: true,
    },
    loan: {
      type: Object,
      required: true,
    },
    open: {
      type: Boolean,
      required: false,
      default: false,
    },
    userRole: {
      type: String,
      required: true,
    },
  },
  computed: {
    borrowerAvatar() {
      const { avatar } = this.loan.borrower.user;
      if (!avatar) {
        return '';
      }

      return `url('${avatar.sizes.thumbnail}')`;
    },
    loanablePrettyName() {
      let article;
      let type;

      switch (this.loan.loanable.type) {
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
    ownerAvatar() {
      const { avatar } = this.loan.loanable.owner.user;
      if (!avatar) {
        return '';
      }

      return `url('${avatar.sizes.thumbnail}')`;
    },
  },
  methods: {
    async completeAction() {
      await this.$store.dispatch('loans/completeAction', this.action);
      this.$emit('completed');
    },
    cancelAction() {
      this.$store.dispatch('loans/cancelAction', this.action);
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
