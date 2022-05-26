<template>
  <b-card no-body class="loan-form loan-actions loan-actions-handover-self-service">
    <b-card-header
      header-tag="header"
      role="tab"
      class="loan-actions__header"
      v-b-toggle.loan-actions-handover-self-service
    >
      <b-row>
        <b-col>
          <h2>
            <svg-waiting v-if="action.status === 'in_process' && !loanIsCanceled" />
            <svg-check v-else-if="action.status === 'completed'" />
            <svg-danger v-else-if="action.status === 'canceled' || loanIsCanceled" />

            Retour
          </h2>
        </b-col>
      </b-row>
      <b-row>
        <b-col>
          <!-- Canceled loans: current step remains in-process. -->
          <span v-if="action.status === 'in_process' && loanIsCanceled">
            Emprunt annulé &bull; {{ item.canceled_at | datetime }}
          </span>
          <span v-else-if="action.status == 'in_process' && !loanIsCanceled"> En attente </span>
          <span v-else-if="action.status === 'completed'">
            Complété &bull; {{ action.executed_at | datetime }}
          </span>
          <span v-else-if="action.status === 'canceled'">
            Contesté &bull; {{ action.executed_at | datetime }}
          </span>
        </b-col>
      </b-row>
    </b-card-header>

    <b-card-body>
      <b-collapse
        id="loan-actions-handover-self-service"
        role="tabpanel"
        accordion="loan-actions"
        :visible="open"
      >
        <div v-if="action.status === 'in_process' && loanIsCanceled">
          <b-alert show variant="danger">
            <p>L'emprunt a été annulé. Cette étape ne peut pas être complétée.</p>
          </b-alert>
        </div>
        <div v-else-if="action.status === 'completed'">
          <b-alert show variant="success">
            <p>L'emprunt s'est clôturé avec succès!</p>
          </b-alert>
        </div>
        <div v-else-if="item.loanable.has_padlock">
          <div class="loan-actions-handover-self-service__text">
            <validation-observer ref="observer" v-slot="{ passes }">
              <b-form
                :novalidate="true"
                class="loan-actions-handover__form"
                @submit.stop.prevent="passes(completeAction)"
              >
                <b-row class="loan-actions-handover__form__image">
                  <b-col v-if="action.image">
                    <p>
                      <a href="#" v-b-modal="'handover-self-service-image'">
                        <img :src="action.image.sizes.thumbnail" />
                      </a>
                    </p>

                    <b-modal
                      size="xl"
                      title="Photo de l'état du véhicule"
                      :id="'handover-self-service-image'"
                      footer-class="d-none"
                    >
                      <img class="img-fit" :src="action.image.url" />
                    </b-modal>
                  </b-col>

                  <b-col>
                    <blockquote v-if="action.comments_by_borrower">
                      {{ action.comments_by_borrower }}
                      <div class="user-avatar" :style="{ backgroundImage: borrowerAvatar }" />
                    </blockquote>
                  </b-col>
                </b-row>

                <b-row v-if="finalPrice > 0">
                  <b-col>
                    <p class="text-center">
                      Coût final du trajet:
                      <span v-b-popover.hover="priceTooltip">{{ finalPrice | currency }}</span
                      >.
                    </p>
                  </b-col>
                </b-row>

                <b-row v-if="!hasEnoughBalance">
                  <b-col>
                    <p>
                      Il manque de crédits à votre compte pour payer cet emprunt.<br />
                      <a href="#" v-b-modal.add-credit-modal>Ajoutez des crédits</a>
                    </p>

                    <b-modal
                      id="add-credit-modal"
                      title="Approvisionner mon compte"
                      size="lg"
                      footer-class="d-none"
                    >
                      <user-add-credit-box
                        :user="user"
                        :minimum-required="finalPrice - user.balance"
                        @bought="reloadUserAndCloseModal"
                        @cancel="closeModal"
                      />
                    </b-modal>
                  </b-col>
                </b-row>

                <div
                  v-if="!action.executed_at && !loanIsCanceled"
                  class="loan-actions-handover-self-service text-center"
                >
                  <b-button
                    size="sm"
                    variant="success"
                    class="mr-3"
                    :disabled="!hasEnoughBalance || actionLoading"
                    @click="completeAction"
                  >
                    Compléter l'emprunt
                  </b-button>
                </div>
              </b-form>
            </validation-observer>
          </div>
        </div>

        <div v-else>
          <p>
            Ce véhicule est mal configuré. Contactez le
            <a href="mailto:support@locomotion.app">support</a>.
          </p>
        </div>
      </b-collapse>
    </b-card-body>
  </b-card>
</template>

<script>
import FormsImageUploader from "@/components/Forms/ImageUploader.vue";
import FormsValidatedInput from "@/components/Forms/ValidatedInput.vue";

import UserAddCreditBox from "@/components/User/AddCreditBox.vue";

import LoanActionsMixin from "@/mixins/LoanActionsMixin";

import { filters } from "@/helpers";

const { currency } = filters;

export default {
  name: "LoanActionsHandoverSelfService",
  mixins: [LoanActionsMixin],
  mounted() {
    const platformTip = parseFloat(this.item.final_platform_tip || this.item.platform_tip);
    this.action.platform_tip = Number.isNaN(platformTip) ? 0 : platformTip;

    if (!this.item.actual_price) {
      this.item.actual_price = 0;
    }

    if (!this.item.actual_insurance) {
      this.item.actual_insurance = 0;
    }
  },
  components: {
    FormsImageUploader,
    FormsValidatedInput,
    UserAddCreditBox,
  },
  computed: {
    finalPrice() {
      const platformTip = parseFloat(this.item.final_platform_tip || this.action.platform_tip);
      return this.item.actual_price + this.item.actual_insurance + platformTip;
    },
    hasEnoughBalance() {
      return this.user.balance >= this.finalPrice;
    },
    priceTooltip() {
      const strParts = [];

      strParts.push(`Trajet: ${currency(this.item.actual_price || 0)}`); // eslint-disable-line no-irregular-whitespace
      if (this.item.actual_insurance > 0) {
        strParts.push(`Assurance: ${currency(this.item.actual_insurance || 0)}`); // eslint-disable-line no-irregular-whitespace
      }

      const platformTip = parseFloat(this.item.final_platform_tip || this.action.platform_tip);
      if (platformTip > 0) {
        strParts.push(`Contribution: ${currency(platformTip)}`); // eslint-disable-line no-irregular-whitespace
      }

      return strParts.join(" \\ ");
    },
  },
  methods: {
    closeModal() {
      this.$bvModal.hide("add-credit-modal");
    },
    async reloadUserAndCloseModal() {
      await this.$store.dispatch("loadUser");
      this.closeModal();
    },
  },
};
</script>

<style lang="scss"></style>
