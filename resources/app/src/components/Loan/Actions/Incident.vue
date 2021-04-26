<template>
  <b-card no-body class="loan-form loan-actions loan-actions-incident"
    :id="`loan-incident-${action.id || 'new'}`">
    <b-card-header header-tag="header" role="tab" class="loan-actions__header">
      <h2 v-b-toggle="`loan-actions-incident-${action.id || 'new'}`">
        <svg-waiting v-if="action.status === 'in_process' && !item.canceled_at" />
        <svg-check v-else-if="action.status === 'completed'" />
        <svg-danger v-else-if="action.status === 'canceled' || item.canceled_at" />

        Incident
      </h2>

      <span v-if="action.status == 'in_process' && !item.canceled_at">En attente</span>
      <span v-else-if="action.status === 'completed'">
        Validé &bull; {{ action.executed_at | datetime }}
      </span>
      <span v-else-if="action.status === 'canceled' || item.canceled_at">
        Contesté &bull; {{ action.executed_at || item.canceled_at | datetime }}
      </span>
    </b-card-header>


    <b-card-body>
      <b-collapse :id="`loan-actions-incident-${action.id || 'new'}`"
        role="tabpanel" accordion="loan-actions"
        :visible="open">
        <div v-if="!action.id">
          <ol v-if="item.loanable.type === 'car'">
            <li><strong>Asseyez-vous et respirez</strong>: voici les étapes à suivre.</li>
            <li>
              <strong>Sélectionnez le type d'incident</strong>:<br>

              Si la voiture est immobilisée et n’est plus en état de rouler, sélectionnez
              <em>Voiture immobilisée</em><br>

              Si les dégâts sont mineurs et que la voiture peut encore rouler, sélectionnez
              <em>Dégâts mineurs</em>
            </li>
            <li><strong>Suivez les instructions à l'étape suivante</strong></li>
            <li><strong>Remise à disposition</strong></li>
          </ol>

          <validation-observer ref="observer" v-slot="{ passes }">
            <b-form :novalidate="true" class="form loan-actions-incident__form"
              @submit.stop.prevent="passes(createAction)"
              @reset.stop.prevent="$emit('reset')">

              <forms-validated-input v-if="incidentTypes.length > 1"
                name="incident_type"
                :rules="{ required: true }"
                label="Type d'incident" type="select"
                :options="incidentTypes"
                v-model="action.incident_type" />

              <forms-validated-input name="comments_on_incident"
                :rules="{ required: true }"
                label="Commentaire" type="textarea" :rows="3"
                v-model="action.comments_on_incident" />

              <div class="loan-actions-incident__buttons text-center">
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
        <div v-else-if="!action.completed_at">
          <div v-if="userRole !== 'owner'">
            <div v-if="action.incident_type === 'accident'">
              <h3>Voiture immobilisée</h3>

              <ol>
                <li>
                  Prévenez Desjardins Assurances en précisant que vous louez la voiture
                  dans le cadre de LocoMotion<br>
                  <strong>Numéro de téléphone pour Desjardins Assurances</strong><br>
                  1 888 PROTÉGÉ (1 888 776-8343)<br>
                  option #4  “Assurance des entreprises”<br>
                  <strong>Numéro du contrat d’assurance (Solon / LocoMotion)</strong><br>
                  59189530
                </li>
                <li>Prévenez le-la propriétaire qu’un dossier est ouvert chez Desjardins</li>
                <li>
                  Desjardins Assurances propose un carrossier pour réaliser les réparations
                  (et éventuellement proposer une voiture de courtoisie au propriétaire,
                  au besoin).
                </li>
              </ol>
            </div>
            <div v-else-if="action.incident_type === 'small_incident'">
              <h3>Dégâts mineurs</h3>

              <ol>
                <li>Prévenez le-la propriétaire</li>
                <li>
                  Le-la propriétaire communiquera avec Desjardins Assurances pour ouvrir un dossier
                </li>
                <li>
                  LocoMotion transmet à Desjardins Assurances les renseignements nécessaires sur
                  votre inscription à LocoMotion et sur la réservation de la voiture.
                </li>
                <li>
                  Desjardins Assurances propose un carrossier pour réaliser les réparations (et
                  éventuellement proposer une voiture de courtoisie au propriétaire, au besoin).
                </li>
              </ol>
            </div>
            <div v-else>
              <h3>Incident avec une remorque our un vélo</h3>

              <blockquote v-if="action.comments_on_incident">
                {{ action.comments_on_incident }}
                <div class="user-avatar" :style="{ backgroundImage: borrowerAvatar }" />
              </blockquote>
            </div>
          </div>

          <div v-if="userRole !== 'borrower'">
            <p>
              {{ borrower.user.name }} mentionne qu'un incident s'est produit.
            </p>

            <div v-if="action.incident_type === 'accident'">
              <h3>Voiture immobilisée</h3>
            </div>
            <div v-else-if="action.incident_type === 'small_incident'">
              <h3>Dégâts mineurs</h3>
            </div>

            <blockquote v-if="action.comments_on_incident">
              {{ action.comments_on_incident }}
              <div class="user-avatar" :style="{ backgroundImage: borrowerAvatar }" />
            </blockquote>
          </div>

          <div class="loan-actions-incident__buttons text-center"
            v-if="user.role === 'admin'">
            <b-button size="sm" variant="success" class="mr-3" @click="completeAction">
              Résoudre
            </b-button>
          </div>
        </div>
        <div v-else>
          <blockquote v-if="action.comments_on_incident">
            {{ action.comments_on_incident }}
            <div class="user-avatar" :style="{ backgroundImage: borrowerAvatar }" />
          </blockquote>

          <p v-if="userRole === 'borrower'">
            Vous avez payé la franchise (ou le montant total de la réparation) et les réparations
            sont terminées. La voiture est remise à la disposition du propriétaire. Vous pouvez à
            nouveau emprunter le véhicule d’un voisin... en espérant que la prochaine fois,
            tout se passe comme sur des roulettes !
          </p>
        </div>
      </b-collapse>
    </b-card-body>
  </b-card>
</template>

<script>
import FormsValidatedInput from '@/components/Forms/ValidatedInput.vue';

import LoanActionsMixin from '@/mixins/LoanActionsMixin';

export default {
  name: 'LoanActionsIncident',
  mixins: [LoanActionsMixin],
  components: {
    FormsValidatedInput,
  },
  mounted() {
    if (this.item.loanable.type !== 'car') {
      this.action.incident_type = 'general';
    }
  },
  computed: {
    incidentTypes() {
      switch (this.item.loanable.type) {
        case 'car':
          return [
            { value: 'accident', text: 'Voiture immobilisée' },
            { value: 'small_incident', text: 'Dégâts mineurs' },
          ];
        default:
          return [
            { value: 'general', text: 'Incident avec une remorque ou un vélo' },
          ];
      }
    },
  },
};
</script>

<style lang="scss">
</style>
