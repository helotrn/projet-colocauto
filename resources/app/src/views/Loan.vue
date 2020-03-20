<template>
  <layout-page name="loan" :loading="!(routeDataLoaded && item && loadedFullLoanable)">
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
            'current-step': isCurrentStep('pre_payment'),
            'reached-step': hasReachedStep('pre_payment'),
          }">
            <svg-check v-if="hasReachedStep('pre_payment')" />
            <svg-waiting v-else />

            <span>Prépaiement</span>
          </li>
          <li :class="{
            'current-step': isCurrentStep('takeover'),
            'reached-step': hasReachedStep('takeover'),
          }">
            <svg-check v-if="hasReachedStep('takeover')" />
            <svg-waiting v-else />

            <span>Prise de possession</span>
          </li>
          <li v-for="incident in item.incidents" :key="incident.id">
            <span>Incident</span>
          </li>
          <li :class="{
            'current-step': isCurrentStep('handover'),
            'reached-step': hasReachedStep('handover'),
          }">
            <svg-check v-if="hasReachedStep('handover')" />
            <svg-waiting v-else />

            <span>Remise du véhicule</span></li>
          </li>
          <li :class="{
            'current-step': isCurrentStep('payment'),
            'reached-step': hasReachedStep('payment'),
          }">
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
              :action="action" :loan="item" :open="isCurrentStep('intention')"
              @completed="loadItem" :user="user" />
            <loan-actions-pre-payment v-else-if="action.type === 'pre_payment'"
              :action="action" :loan="item" :open="isCurrentStep('pre_payment')"
              @completed="loadItem" :user="user" />
            <loan-actions-takeover v-else-if="action.type === 'takeover'"
              :action="action" :loan="item" :open="isCurrentStep('takeover')"
              @completed="loadItem" :user="user" />
            <loan-actions-handover v-else-if="action.type === 'handover'"
              :action="action" :loan="item" :open="isCurrentStep('handover')"
              @completed="loadItem" :user="user" />
            <loan-actions-payment v-else-if="action.type === 'payment'"
              :action="action" :loan="item" :open="isCurrentStep('payment')"
              @completed="loadItem" :user="user" />
            <span v-else>
              {{ action.type }} n'est pas supporté. Contactez le
              <a href="mailto:support@locomotion.app">support</a>.
            </span>
          </div>
        </div>
      </b-col>
    </b-row>
  </layout-page>
</template>

<script>
import Check from '@/assets/svg/check.svg';
import Waiting from '@/assets/svg/waiting.svg';

import LoanForm from '@/components/Loan/Form.vue';
import LoanActionsHandover from '@/components/Loan/Actions/Handover.vue';
import LoanActionsIntention from '@/components/Loan/Actions/Intention.vue';
import LoanActionsPayment from '@/components/Loan/Actions/Payment.vue';
import LoanActionsPrePayment from '@/components/Loan/Actions/PrePayment.vue';
import LoanActionsTakeover from '@/components/Loan/Actions/Takeover.vue';

import Authenticated from '@/mixins/Authenticated';
import DataRouteGuards from '@/mixins/DataRouteGuards';
import FormMixin from '@/mixins/FormMixin';

import { capitalize } from '@/helpers/filters';

export default {
  name: 'Loan',
  mixins: [Authenticated, DataRouteGuards, FormMixin],
  components: {
    LoanForm,
    LoanActionsHandover,
    LoanActionsIntention,
    LoanActionsPayment,
    LoanActionsPrePayment,
    LoanActionsTakeover,
    'svg-check': Check,
    'svg-waiting': Waiting,
  },
  beforeRouteEnter(to, from, next) {
    next((vm) => {
      if (vm.id === 'new' && !vm.item.loanable) {
        vm.$router.replace('/community/list');
      }
    });
  },
  data() {
    return {
      loadedFullLoanable: false,
    };
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
        case 'trailer':
          return '';
        default:
          return '';
      }
    },
    loanableOwnerText() {
      if (this.userIsOwner) {
        return '';
      }

      const ownerName = this.item.loanable.owner.user.name;
      const particle = ['a', 'e', 'i', 'o', 'u', 'é', 'è']
        .indexOf(ownerName[0]
          .toLowerCase()) > -1 ? "d'" : 'de ';
      return `${particle}${ownerName}`;
    },
    loanablePrettyName() {
      let particle;
      let type;

      switch (this.item.loanable.type) {
        case 'car':
          particle = 'de la ';
          type = 'voiture';
          break;
        case 'bike':
          particle = 'du ';
          type = 'vélo';
          break;
        case 'trailer':
          particle = 'de la ';
          type = 'remorque';
          break;
        default:
          particle = "de l'";
          type = 'objet';
          break;
      }

      if (this.user.id === this.item.loanable.owner.user.id) {
        particle = 'de votre ';
      }

      const description = `${particle}${type}`;
      return `${description} ${this.loanableOwnerText}`;
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
    userIsOwner() {
      return this.user.id === this.item.loanable.owner.user.id;
    },
  },
  methods: {
    async formMixinCallback() {
      const { id, type } = this.item.loanable;
      await this.$store.dispatch(`${type}s/retrieveOne`, {
        params: {
          fields: '*,owner.id,owner.user.id,owner.user.avatar,owner.user.name',
          '!fields': 'events',
        },
        id,
      });
      const loanable = this.$store.state[`${type}s`].item;

      this.$store.commit(`${type}s/item`, null);

      this.$store.commit(`${this.slug}/mergeItem`, { loanable });

      this.loadedFullLoanable = true;
    },
    hasReachedStep(step) {
      const { id, actions } = this.item;
      const intention = actions.find(a => a.type === 'intention');
      const prePayment = actions.find(a => a.type === 'pre_payment');
      const takeover = actions.find(a => a.type === 'takeover');
      const handover = actions.find(a => a.type === 'handover');
      const payment = actions.find(a => a.type === 'payment');

      switch (step) {
        case 'creation':
          return !!id;
        case 'intention':
          return intention && intention.executed_at;
        case 'pre_payment':
          return prePayment && prePayment.executed_at;
        case 'takeover':
          return takeover && takeover.executed_at;
        case 'handover':
          return handover && handover.executed_at;
        case 'payment':
          return payment && payment.executed_at;
        default:
          return false;
      }
    },
    isCurrentStep(step) {
      const { id, actions } = this.item;
      const intention = actions.find(a => a.type === 'intention');
      const prePayment = actions.find(a => a.type === 'pre_payment');
      const takeover = actions.find(a => a.type === 'takeover');
      const handover = actions.find(a => a.type === 'handover');
      const payment = actions.find(a => a.type === 'payment');

      switch (step) {
        case 'creation':
          return !id;
        case 'intention':
          return intention && !intention.executed_at;
        case 'pre_payment':
          return prePayment && !prePayment.executed_at;
        case 'takeover':
          return takeover && !takeover.executed_at;
        case 'handover':
          return handover && !handover.executed_at;
        case 'payment':
          return payment && payment.executed_at;
        default:
          return false;
      }
    },
    skipLoadItem() {
      if (this.id === 'new') {
        return true;
      }

      return false;
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
