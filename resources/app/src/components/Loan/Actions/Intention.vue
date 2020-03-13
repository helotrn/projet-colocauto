<template>
  <b-card no-body class="loan-form loan-actions loan-actions-intention">
    <b-card-header header-tag="header" role="tab" class="loan-actions__header">
      <h2 v-b-toggle.loan-actions-intention>
        <svg-waiting v-if="action.status === 'in_process'" />
        <svg-check v-else-if="action.status === 'completed'" />
        <svg-cancel v-else-if="action.status === 'canceled'" />

        Confirmation de l'emprunt
      </h2>

      <span v-if="action.status == 'in_process'">En attente d'approbation</span>
      <span v-else-if="action.status === 'completed'">Approuvé &bull; {{ action.executed_at | datetime }}</span>
      <span v-else-if="action.status === 'canceled'">Refusé &bull; {{ action.executed_at | datetime }}</span>
    </b-card-header>


    <b-card-body>
      <b-collapse id="loan-actions-intention" role="tabpanel" accordion="loan-actions" :visible="open">
        <div class="loan-actions-intention__image mb-3 text-center">
          <div :style="{ backgroundImage: borrowerAvatar }" />
        </div>

        <div class="loan-actions-intention__description text-center mb-3">
          <p>
            {{ loan.borrower.user.name }} veut vous emprunter {{ loanablePrettyName }}
          </p>

          <label>Raison de l'emprunt</label>
          <p>{{ loan.reason }}</p>

          <blockquote v-if="loan.message_for_owner">
            {{ loan.message_for_owner }}
          </blockquote>
        </div>

        <div class="loan-actions-intention__see-details text-center mb-3">
          <b-button size="sm" variant="outline-info" v-b-toggle.loan-actions-new>
            Voir les détails
          </b-button>
        </div>

        <div class="loan-actions-intention__message_for_borrower text-center mb-3">
          <forms-validated-input type="textarea" name="message_for_borrower"
            v-model="message_for_borrower"
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
  },
  data() {
    return {
      message_for_borrower: '',
    };
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
      switch (this.loan.loanable.type) {
        case 'car':
          return 'votre voiture';
        case 'bike':
          return 'votre vélo';
        case 'trailer':
          return 'votre remorque';
        default:
          return 'votre objet';
      }
    },
  },
  methods: {
    completeAction() {
      this.$store.dispatch('loans/completeAction', this.action);
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
