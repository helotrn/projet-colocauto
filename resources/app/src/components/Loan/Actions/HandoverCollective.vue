<template>
  <b-card no-body class="loan-form loan-actions loan-actions-handover-collective">
    <b-card-header header-tag="header" role="tab" class="loan-actions__header"
      v-b-toggle.loan-actions-handover-collective>
      <h2>
        <svg-waiting v-if="action.status === 'in_process'" />
        <svg-check v-else-if="action.status === 'completed'" />
        <svg-danger v-else-if="action.status === 'canceled'" />

        Retour
      </h2>

      <span v-if="action.status == 'in_process'">En attente</span>
      <span v-else-if="action.status === 'completed'">
        Complété &bull; {{ action.executed_at | datetime }}
      </span>
      <span v-else-if="action.status === 'canceled'">
        Contesté &bull; {{ action.executed_at | datetime }}
      </span>
    </b-card-header>

    <b-card-body>
      <b-collapse id="loan-actions-handover-collective" role="tabpanel" accordion="loan-actions"
        :visible="open">
        <div v-if="item.loanable.has_padlock">
          <b-alert show variant="danger">
            <div class="alert-heading"><h4>Attention</h4></div>

            <div>
              <p class="mb-0">
                En complétant cette étape, vous signalez que vous avez bien remis le
                véhicule à son emplacement. Vous ne pourrez plus ouvrir le cadenas NOKE
                associé au véhicule.
              </p>
            </div>
          </b-alert>

          <b-alert show variant="info">
            <p>Vous avez un problème avec le cadenas?</p>
            <p>
              Contactez-nous entre 9h et 20h au 438-476-3343<br>
              (cette ligne est dédiée uniquement aux problèmes liés aux cadenas)
            </p>
          </b-alert>

          <b-alert show variant="warning">
            <div class="alert-heading"><h4>En cas de retard</h4></div>

            <div>
              <p class="mb-0">
                Si vous avez un retard, utilisez le bouton
                &laquo;&nbsp;Signaler un retard&nbsp;&raquo; sur la plateforme ou
                <a @click.prevent="$emit('extension')" href="#">cliquez ici</a>.
              </p>
            </div>
          </b-alert>

          <div class="loan-actions-handover-collective__text">
            <p>
              Vous avez ramené le véhicule? Alors, il ne vous reste plus qu'à clôturer
              l'emprunt en remplissant cette étape.
            </p>

            <p>
              Un souci avec un véhicule?  Décrivez le problème et/ou chargez la photo
              ci-dessous.
            </p>

            <p>
              D'autres idées pour locomotion? Contactez
              <a href="mailto:info@locomotion.app">info@locomotion.app</a>
              ou faites-nous part de vos suggestions pour la plateforme par ici.
            </p>

            <p class="text-center"><strong>Merci et à très bientôt !</strong></p>

            <validation-observer ref="observer" v-slot="{ passes }">
              <b-form :novalidate="true" class="register-form__form"
                @submit.stop.prevent="passes(completeAction)">
                <b-row v-if="!action.executed_at">
                  <b-col lg="6">
                    <forms-image-uploader
                      label="Photo du véhicule"
                      field="image"
                      v-model="action.image" />
                  </b-col>

                  <b-col lg="6">
                    <forms-validated-input
                      v-if="userRole === 'borrower'"
                      id="comments_by_borrower" name="comments_by_borrower"
                      type="textarea" :rows="6" :disabled="!!action.commented_by_borrower_at"
                      label="Laissez un commentaire (facultatif)"
                      placeholder="Commentaire sur l'emprunt"
                      v-model="action.comments_by_borrower" />
                  </b-col>
                </b-row>

                <b-row v-else>
                  <b-col v-if="action.image">
                    <p>
                      <a href="#" v-b-modal="'handover-collective-image'">
                        <img :src="action.image.sizes.thumbnail">
                      </a>
                    </p>

                    <b-modal size="xl"
                      title="Photo de l'état du véhicule"
                      :id="'handover-collective-image'" footer-class="d-none">
                      <img class="img-fit" :src="action.image.url">
                    </b-modal>
                  </b-col>

                  <b-col>
                    <blockquote v-if="action.comments_by_borrower">
                      {{ action.comments_by_borrower }}
                      <div class="user-avatar" :style="{ backgroundImage: borrowerAvatar }" />
                    </blockquote>
                  </b-col>
                </b-row>

                <b-row>
                  <b-col>
                    <p class="text-center">
                      Coût final du trajet:
                      <span v-b-popover.hover="priceTooltip">{{ finalPrice | currency }}</span>.
                    </p>
                  </b-col>
                </b-row>

                <b-row v-if="!hasEnoughBalance">
                  <b-col>
                    <p>
                      Il manque de crédits à votre compte pour payer cet emprunt.<br>
                      <a href="#" v-b-modal.add-credit-modal>Ajoutez des crédits</a>
                    </p>

                    <b-modal id="add-credit-modal" title="Approvisionner mon compte" size="lg"
                      footer-class="d-none">
                      <user-add-credit-box :user="user"
                        :minimum-required="finalPrice - user.balance"
                        @bought="reloadUserAndCloseModal" @cancel="closeModal"/>
                    </b-modal>
                  </b-col>
                </b-row>

                <div v-if="!action.executed_at"
                  class="loan-actions-handover-collective text-center">
                  <b-button size="sm" variant="success" class="mr-3"
                    :disabled="!hasEnoughBalance" @click="completeAction">
                    Mon emprunt est terminé!
                  </b-button>
                </div>

                <b-row class="loan-actions__alert"
                  v-if="!action.executed_at && hasEnoughBalance">
                  <b-col>
                    <b-alert variant="warning" show>
                      Dans 48h, vous ne pourrez plus modifier vos informations.
                      Nous validerons le coût de l'emprunt avec les détails ci-dessus.
                    </b-alert>
                  </b-col>
                </b-row>
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
import FormsImageUploader from '@/components/Forms/ImageUploader.vue';
import FormsValidatedInput from '@/components/Forms/ValidatedInput.vue';

import UserAddCreditBox from '@/components/User/AddCreditBox.vue';

import LoanActionsMixin from '@/mixins/LoanActionsMixin';

import { filters } from '@/helpers';

const { currency } = filters;

export default {
  name: 'LoanActionsHandoverCollective',
  mixins: [LoanActionsMixin],
  mounted() {
    this.action.platform_tip = parseFloat(
      this.item.final_platform_tip || this.item.platform_tip,
      10,
    );
  },
  components: {
    FormsImageUploader,
    FormsValidatedInput,
    UserAddCreditBox,
  },
  computed: {
    finalPrice() {
      return this.item.actual_price
        + this.item.actual_insurance
        + this.action.platform_tip;
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

      const platformTip = parseFloat(this.item.final_platform_tip || this.action.platform_tip, 10);
      if (platformTip > 0) {
        strParts.push(`Contribution: ${currency(platformTip)}`); // eslint-disable-line no-irregular-whitespace
      }

      return strParts.join(' \\ ');
    },
  },
  methods: {
    closeModal() {
      this.$bvModal.hide('add-credit-modal');
    },
    async reloadUserAndCloseModal() {
      await this.$store.dispatch('loadUser');
      this.closeModal();
    },
  },
};
</script>

<style lang="scss">
</style>
