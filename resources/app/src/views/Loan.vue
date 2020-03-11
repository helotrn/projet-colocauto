<template>
  <layout-page name="loan" v-if="item">
    <vue-headful :title="fullTitle" />

    <b-row>
      <b-col>
        <h1>Emprunt {{ loanablePrettyName }}</h1>
      </b-col>
    </b-row>

    <b-row>
      <b-col>
        <p>
          {{ prettyType }} {{ loanableDescription }} {{ loanableOwnerText }}
        </p>
        <p>
          {{ item.departure_at | day | capitalize }} {{ item.departure_at | date }}
          &bull;
          {{ item.departure_at | time }} à {{ returnAt | time }}
        </p>
      </b-col>
    </b-row>

    <b-row>
      <b-col lg="3" class="loan__sidebar">
        <ul class="loan__sidebar__actions">
          <li><svg-check /> Demande d'emprunt</li>
          <li><svg-waiting /> Confirmation de l'emprunt</li>
          <li><svg-waiting /> Paiement</li>
          <li><svg-waiting /> Prise de possession</li>
          <li v-for="incident in item.incidents" :key="incident.id"><svg-waiting /> Incident</li>
          <li><svg-waiting /> Remise du véhicule</li>
          <li><svg-waiting /> Conclusion</li>
        </ul>
      </b-col>

      <b-col lg="9">
        <h2>Informations sur l'emprunt</h2>

        <div class="loan__actions">
          <loan-form :loan="item" />

          <div class="loan__actions__action" v-for="action in item.actions" :key="action.id">
            <loan-actions-intention v-if="action.type === 'intention'" :action="action" />
          </div>
        </div>
      </b-col>
    </b-row>
    {{ item }}
  </layout-page>
</template>

<script>
import Check from '@/assets/svg/check.svg';
import Waiting from '@/assets/svg/waiting.svg';

import LoanForm from '@/components/Loan/Form.vue';
import LoanActionsIntention from '@/components/Loan/Actions/Intention.vue';

import FormMixin from '@/mixins/FormMixin';

import { capitalize } from '@/helpers/filters';

export default {
  name: 'Loan',
  mixins: [FormMixin],
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
};
</script>

<style lang="scss">
.loan.page {
  .page__content {
    padding-top: 45px;
    padding-bottom: 45px;
  }

  .loan__actions {
    .card {
      margin-bottom: 20px;
    }
  }
}
</style>
