<template>
  <div class="dashboard-balance">
    <h3>
      Solde
      <b-badge pill variant="light" v-b-popover.hover="$t('approvisionner_popover')"> ? </b-badge>
    </h3>

    <div class="dashboard-balance__balance">{{ user.balance | currency }}</div>

    <div class="dashboard-balance__buttons">
      <b-button class="mr-3" size="sm" variant="primary" v-b-modal.add-credit-modal>
        {{ $t("approvisionner") | capitalize }}
      </b-button>

      <span
        v-if="user.balance > 0 && user.balance < 10"
        tabindex="0"
        v-b-tooltip.hover
        :title="$t('reclamer_tooltip')"
      >
        <b-button size="sm" variant="outline-primary" disabled>
          {{ $t("reclamer") | capitalize }}
        </b-button>
      </span>

      <b-button v-if="user.balance >= 10" size="sm" variant="outline-primary" v-b-modal.claim-modal>
        {{ $t("reclamer") | capitalize }}
      </b-button>
    </div>

    <b-modal
      id="add-credit-modal"
      title="Approvisionner mon compte"
      size="lg"
      footer-class="d-none"
    >
      <user-add-credit-box
        :payment-methods="user.payment_methods"
        @bought="reloadUserAndCloseModal"
        @cancel="closeModal"
      />
    </b-modal>

    <b-modal
      id="claim-modal"
      title="Demande de transfert du solde du compte LocoMotion vers un compte bancaire"
      size="md"
      footer-class="d-none"
    >
      <user-claim-credits-box
        :user="user"
        @claimed="reloadUserAndCloseModal"
        @cancel="closeModal"
      />
    </b-modal>
  </div>
</template>

<script>
import locales from "@/locales";

import UserAddCreditBox from "@/components/User/AddCreditBox.vue";
import UserClaimCreditsBox from "@/components/User/ClaimCreditsBox.vue";

export default {
  name: "DashboardBalance",
  components: {
    UserAddCreditBox,
    UserClaimCreditsBox,
  },
  props: {
    user: {
      type: Object,
      required: true,
    },
  },
  methods: {
    closeModal() {
      this.$bvModal.hide("add-credit-modal");
      this.$bvModal.hide("claim-modal");
    },
    async reloadUser() {
      await this.$store.dispatch("loadUser");
    },
    async reloadUserAndCloseModal() {
      this.closeModal();
      await this.reloadUser();
    },
  },
  i18n: {
    messages: {
      fr: {
        ...locales.fr.components.dashboard.balance,
      },
    },
  },
};
</script>

<style lang="scss">
.dashboard-balance {
  h3 {
    .badge {
      cursor: pointer;
      position: relative;
      top: -2px;
      margin-left: 6px;
      .badge-pill {
        padding-left: 0.4em;
        padding-right: 0.4em;
      }
    }
  }

  .btn {
    margin-left: 0;
    margin-bottom: 10px;
  }

  &__balance {
    font-size: 40px;
    font-weight: 500;
    margin-bottom: 15px;
  }

  &__buttons {
    margin-bottom: 10px;
  }
}
</style>
