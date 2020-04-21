<template>
  <div class="dashboard-balance">
    <h3>
      Solde
      <b-badge pill variant="light" v-b-popover.hover="$t('approvisionner_popover')">
        ?
      </b-badge></h3>

    <div class="dashboard-balance__balance">
      {{ roundedBalance }}&nbsp;$
    </div>

    <div class="dashboard-balance__buttons">
      <b-button class="mr-3" size="sm" variant="primary" v-b-modal.add-credit-modal>
        {{ $t('approvisionner') }}
      </b-button>

      <span v-if="user.balance < 10"
        tabindex="0"  v-b-tooltip.hover :title="$t('reclamer_tooltip')">
        <b-button size="sm" variant="outline-primary" disabled>
          {{ $t('reclamer') }}
        </b-button>
      </span>
      <b-button v-else size="sm" variant="outline-primary">
        {{ $t('reclamer') }}
      </b-button>
    </div>

    <p class="dashboard-balance__payment-methods">
      <router-link to="/profile/payment_methods">
        {{ $t('modify_payment_method') }}
      </router-link>
    </p>

    <b-modal id="add-credit-modal" title="Approvisionner mon compte" size="lg"
      footer-class="d-none">
      <user-add-credit-box :user="user" @bought="reloadUserAndCloseModal" @cancel="closeModal"/>
    </b-modal>
  </div>
</template>

<i18n>
fr:
  approvisionner: Approvisionner
  modify_payment_method: Modifier les modes de paiement
  reclamer: Réclamer
  reclamer_tooltip: Un minimum de 10$ est requis pour réclamer son solde.
  approvisionner_popover: |
    On aime l'idée de « monnaie locale », une autre manière d'aider les échanges dans le quartier
    et le commerce local. Une opportunité d'essayer une économie à visage humain? Finalement, c'est
    facile. C'est comme mettre des billets sur sa carte de transport collectif :-)
</i18n>

<script>
import UserAddCreditBox from '@/components/User/AddCreditBox.vue';

export default {
  name: 'DashboardBalance',
  components: {
    UserAddCreditBox,
  },
  props: {
    user: {
      type: Object,
      required: true,
    },
  },
  computed: {
    roundedBalance() {
      return Math.floor(this.user.balance);
    },
  },
  methods: {
    closeModal() {
      this.$bvModal.hide('add-credit-modal');
    },
    async reloadUser() {
      await this.$store.dispatch('loadUser');
    },
    async reloadUserAndCloseModal() {
      this.closeModal();
      await this.reloadUser();
    },
  },
};
</script>

<style lang="scss">
.dashboard-balance {
  h3 {
    font-size: 20px;
    font-weight: normal;
    font-weight: 600;

    .badge {
      cursor: pointer;
    }
  }

  &__balance {
    font-size: 40px;
    font-weight: 500;
    margin-bottom: 15px;
  }

  &__buttons {
    margin-bottom: 10px;
  }

  &__payment-methods {
    font-size: 13px;
  }
}
</style>
