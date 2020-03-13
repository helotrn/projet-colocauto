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
          <li :class="{ 'current-step': isCurrentStep('creation') }">
            <svg-check v-if="hasReachedStep('creation')" />
            <svg-waiting v-else />

            <span>Demande d'emprunt</span>
          </li>
          <li>
            <svg-check v-if="hasReachedStep('intention')" />
            <svg-waiting v-else />

            <span>Confirmation de l'emprunt</span>
          </li>
          <li>
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
            <loan-actions-intention v-if="action.type === 'intention'" :action="action" />
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
    loanable() {
      return this.item.loanable;
    },
    loanForm() {
      return this.$store.state.loans.form;
    },
    loanableDescription() {
      switch (this.loanable.type) {
        case 'car':
          return `${this.loanable.brand} ${this.loanable.model} `
            + `${this.loanable.year_of_circulation}`;
        case 'bike':
          break;
        case 'trailer':
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
      switch (this.loanable.type) {
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
        capitalize(this.$i18n.t('titles.profile')),
        capitalize(this.$i18n.tc('véhicule', 2)),
      ];

      if (this.pageTitle) {
        parts.push(this.pageTitle);
      }

      return parts.reverse().join(' | ');
    },
    pageTitle() {
      return this.item.name || capitalize(this.$i18n.tc('véhicule', 1));
    },
    prettyType() {
      switch (this.loanable.type) {
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
    async submitLoan() {
      await this.submit();
      await this.$store.dispatch('loadUser');
    },
    hasReachedStep(step) {
      switch (step) {
        case 'creation':
          return !!this.item.id;
        default:
          return false;
      }
    },
    isCurrentStep(step) {
      switch (step) {
        case 'creation':
          return !this.item.id;
        default:
          return false;
      }
    }
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

        li.current-step {
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
