<template>
  <b-card no-body class="loan-form loan-actions loan-actions-takeover-collective">
    <b-card-header header-tag="header" role="tab" class="loan-actions__header"
      v-b-toggle.loan-actions-takeover-collective>
      <h2>
        <svg-waiting v-if="action.status === 'in_process'" />
        <svg-check v-else-if="action.status === 'completed'" />
        <svg-danger v-else-if="action.status === 'canceled'" />

        Informations avant de partir
      </h2>

      <span v-if="action.status == 'in_process'">En attente</span>
      <span v-else-if="action.status === 'completed'">
        Complété &bull; {{ action.executed_at | datetime }}
      </span>
      <span v-else-if="action.status === 'canceled'">
        Annulé &bull; {{ action.executed_at | datetime }}
      </span>
    </b-card-header>

    <b-card-body>
      <b-collapse id="loan-actions-takeover-collective" role="tabpanel" accordion="loan-actions"
        :visible="open">
        <b-row>
          <b-col>
            <loan-covid-collapsible-section />
          </b-col>
        </b-row>

        <div v-if="item.loanable.has_padlock">
          <b-row>
            <b-col>
              <b-alert show variant="danger">
                <div class="alert-heading"><h4>À savoir</h4></div>

                <div>
                  <p>
                    Le cadenas du véhicule sera automatiquement associé à votre application Noke
                    Pro pour la durée de votre réservation. Il peut y avoir un délai de quelques
                    minutes entre la réservation et la synchronisation de vos accès aux cadenas.
                  </p>

                  <p class="mb-0">Merci de le prendre en compte lors de votre réservation.</p>
                </div>
              </b-alert>
            </b-col>
          </b-row>

          <b-row>
            <b-col>
              <h4>Et après ?</h4>

              <p>
                Après utilisation du véhicule, merci de venir clôturer votre emprunt sur la
                plateforme via la section suivante &laquo;&nbsp;Retour&nbsp;&raquo;.
              </p>

              <p>
                Si vous avez du retard, utilisez le bouton
                &laquo;&nbsp;Signaler un retard&nbsp;&raquo; sur la plateforme.
              </p>

              <p>
                Prenez soin
                <span v-if="item.loanable.type === 'bike'">du vélo.</span>
                <span v-else-if="item.loanable.type === 'trailer'">de la remorque.</span>
                <span v-else-if="item.loanable.type === 'car'">de la voiture.</span>
                <span v-else>du véhicule.</span>
                <br>
                Si vous identifiez un problème, prenez une photo et/ou notez
                le problème, vous pourrez nous en aviser à votre retour.
              </p>

              <p>
                En cas de problème, contactez
                <a href="mailto:info@locomotion.app">info@locomotion.app</a>
              </p>

              <p class="text-center"><strong>Bonne route!</strong></p>
            </b-col>
          </b-row>

          <b-row class="loan-actions-takeover-collective__buttons text-center"
            v-if="!action.executed_at">
            <b-col>
              <b-button @click="completeAction" size="sm" variant="success">
                J'ai bien lu ces infos!
              </b-button>
            </b-col>
          </b-row>
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
import LoanCovidCollapsibleSection from '@/components/Loan/CovidCollapsibleSection.vue';

import LoanActionsMixin from '@/mixins/LoanActionsMixin';

export default {
  name: 'LoanActionsTakeoverCollective',
  mixins: [LoanActionsMixin],
  components: {
    LoanCovidCollapsibleSection,
  },
};
</script>

<style lang="scss">
</style>
