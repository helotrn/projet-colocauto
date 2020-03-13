<template>
  <layout-page name="loan" v-if="routeDataLoaded && item">
    <vue-headful :title="fullTitle" />

    <b-row>
      <b-col>
        <h1>Emprunt {{ loanablePrettyName }}</h1>
      </b-col>
    </b-row>

    <b-row>
      <b-col class="loan__description">
        <p>
          {{ prettyType }} {{ loanableDescription }} {{ loanableOwnerText }}
          <br>
          {{ item.departure_at | day | capitalize }} {{ item.departure_at | date }}
          &bull;
          {{ item.departure_at | time }} à {{ returnAt | time }}
        </p>
      </b-col>
    </b-row>

    <b-row>
      <b-col lg="3" class="loan__sidebar">
        <ul class="loan__sidebar__actions">
          <li :class="{
            'current-step': isCurrentStep('creation'),
            'reached-step': hasReachedStep('creation'),
          }">
            <svg-check v-if="hasReachedStep('creation')" />
            <svg-waiting v-else />

            <span>Demande d'emprunt</span>
          </li>
          <li :class="{
            'current-step': isCurrentStep('intention'),
            'reached-step': hasReachedStep('intention'),
          }">
            <svg-check v-if="hasReachedStep('intention')" />
            <svg-waiting v-else />

            <span>Confirmation de l'emprunt</span>
          </li>
          <li :class="{
            'current-step': isCurrentStep('account'),
            'reached-step': hasReachedStep('account'),
          }">
            <svg-check v-if="hasReachedStep('account')" />
            <svg-waiting v-else />

            <span>Paiement</span>
          </li>
          <li>
            <svg-check v-if="hasReachedStep('takeover')" />
            <svg-waiting v-else />

            <span>Prise de possession</span>
          </li>
          <li v-for="incident in item.incidents" :key="incident.id">
            <span>Incident</span>
          </li>
          <li>
            <svg-check v-if="hasReachedStep('handover')" />
            <svg-waiting v-else />

            <span>Remise du véhicule</span></li>
          </li>
          <li>
            <svg-check v-if="hasReachedStep('payment')" />
            <svg-waiting v-else />

            <span>Conclusion</span></li>
          </li>
        </ul>
      </b-col>

      <b-col lg="9" class="loan__actions">
        <h2>Informations sur l'emprunt</h2>

        <div>
          <loan-form :loan="item" :form="loanForm" :open="isCurrentStep('creation')"
            @submit="submitLoan" />

          <div class="loan__actions__action" v-for="action in item.actions" :key="action.id">
            <loan-actions-intention v-if="action.type === 'intention'"
              :action="action" :loan="item" :open="isCurrentStep('intention')" />
            <span v-else>
              {{ action.type }}
            </span>
          </div>
        </div>
      </b-col>
    </b-row>
  </layout-page>
  <layout-loading v-else />
</template>

<script>
import Check from '@/assets/svg/check.svg';
import Waiting from '@/assets/svg/waiting.svg';

import LoanForm from '@/components/Loan/Form.vue';
import LoanActionsIntention from '@/components/Loan/Actions/Intention.vue';

import DataRouteGuards from '@/mixins/DataRouteGuards';
import FormMixin from '@/mixins/FormMixin';

import { capitalize } from '@/helpers/filters';

export default {
  name: 'Loan',
  mixins: [DataRouteGuards, FormMixin],
  components: {
    LoanForm,
    LoanActionsIntention,
    'svg-check': Check,
    'svg-waiting': Waiting,
  },
  computed: {
    loanForm() {
      return this.$store.state.loans.form;
    },
    loanableDescription() {
      switch (this.item.loanable.type) {
        case 'car':
          return `${this.item.loanable.brand} ${this.item.loanable.model} `
            + `${this.item.loanable.year_of_circulation}`;
        case 'bike':
          return `${this.item.loanable.model}`;
          break;
        case 'trailer':
          return '';
          break;
        default:
          break;
      }

      return '';
    },
    loanableOwnerText() {
      const ownerName = this.item.loanable.owner.user.name;
      const particle = ['a', 'e', 'i', 'o', 'u', 'é', 'è']
        .indexOf(ownerName[0]
          .toLowerCase()) > -1 ? "d'" : 'de ';
      return `${particle}${ownerName}`;
    },
    loanablePrettyName() {
      let type;
      switch (this.item.loanable.type) {
        case 'car':
          type = 'de la voiture';
          break;
        case 'bike':
          type = 'du vélo';
          break;
        case 'trailer':
          type = 'de la remorque';
          break;
        default:
          type = "l'objet";
          break;
      }

      return `${type} ${this.loanableOwnerText}`;
    },
    fullTitle() {
      const parts = [
        'LocoMotion',
        capitalize(this.$i18n.tc('titles.loan', 2)),
      ];

      if (this.pageTitle) {
        parts.push(this.pageTitle);
      }

      return parts.reverse().join(' | ');
    },
    pageTitle() {
      return this.item.loanable ? this.item.loanable.name : capitalize(this.$i18n.tc('titles.loanable', 1));
    },
    prettyType() {
      switch (this.item.loanable.type) {
        case 'car':
          return 'Voiture';
        case 'bike':
          return 'Vélo';
        case 'trailer':
          return 'Remorque';
        default:
          return 'Objet';
      }
    },
    returnAt() {
      return this.$dayjs(this.item.departure_at)
        .add(this.item.duration_in_minutes, 'minute')
        .format('YYYY-MM-DD HH:mm:ss');
    },
  },
  methods: {
    hasReachedStep(step) {
      const { id, actions } = this.item;
      const intention = actions.find(a => a.type === 'intention');

      switch (step) {
        case 'creation':
          return !!id;
        case 'intention':
          return intention && intention.accepted_at;
        default:
          return false;
      }
    },
    isCurrentStep(step) {
      const { id, actions } = this.item;
      const intention = actions.find(a => a.type === 'intention');

      switch (step) {
        case 'creation':
          return !id;
        case 'intention':
          return intention && !intention.accepted_at;
        default:
          return false;
      }
    },
    skipLoadItem() {
      return !this.item.id && this.item.loanable;
    },
    async submitLoan() {
      await this.submit();
      await this.$store.dispatch('loadUser');
    },
  },
};
</script>

<style lang="scss">
.loan.page {
  h1 {
    margin-bottom: 30px;
  }

  .loan {
    &__sidebar {
      &__actions {
        padding: 0;
        list-style-type: none;

        li {
          line-height: 19px;
          margin-bottom: 10px;

          opacity: 0.5;
        }

        li.current-step,
        li.reached-step {
          opacity: 1;
        }

        li span {
          position: relative;
          top: 5px;
        }

        li svg {
          width: 19px;
          height: 19px;
          margin-right: 6px;
        }
      }
    }

    &__description {
      font-size: 20px;
      font-weight: 600;
      line-height: 30px;
      margin-bottom: 30px;
    }

    &__actions {
      > h2 {
        margin-bottom: 60px;
      }

      .card {
        margin-bottom: 20px;
      }
    }
  }

  .page__content {
    padding-top: 45px;
    padding-bottom: 45px;
  }
}
</style>
