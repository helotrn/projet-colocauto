<template>
  <layout-page name="loan" :loading="!pageLoaded">
    <div v-if="pageLoaded">
      <vue-headful :title="fullTitle" />

      <loan-header :user="user" :loan="item" />

      <b-row>
        <b-col lg="3" class="loan__sidebar">
          <loan-menu :item="item" />
        </b-col>

        <b-col lg="9" class="loan__actions">
          <div class="loan__actions__buttons text-right mb-3"
            v-if="!!item.id && item.loan_status === 'in_process'">
            <b-button class="ml-3 mb-3" variant="danger" :disabled="hasReachedStep('takeover')"
              @click="cancelLoan">
              Annuler la réservation
            </b-button>
            <b-button v-if="!userIsOwner" class="ml-3 mb-3" variant="warning"
              :disabled="!hasReachedStep('takeover') || hasReachedStep('payment')"
              @click="addExtension">
              Signaler un retard
            </b-button>
            <b-button v-if="!userIsOwner" class="ml-3 mb-3" variant="warning"
              :disabled="!hasReachedStep('takeover')"
              @click="addIncident('accident')">
              Signaler un incident
            </b-button>
          </div>

          <loan-actions :item="item" @load="loadItemAndUser" :form="loanForm"
            :user="user" @submit="submitLoan" @extension="addExtension" />
        </b-col>
      </b-row>
    </div>
  </layout-page>
</template>

<script>
import LoanActions from '@/components/Loan/Actions.vue';
import LoanHeader from '@/components/Loan/LoanHeader.vue';
import LoanMenu from '@/components/Loan/Menu.vue';

import Authenticated from '@/mixins/Authenticated';
import DataRouteGuards from '@/mixins/DataRouteGuards';
import FormMixin from '@/mixins/FormMixin';
import LoanStepsSequence from '@/mixins/LoanStepsSequence';
import UserMixin from '@/mixins/UserMixin';

import { capitalize } from '@/helpers/filters';

export default {
  name: 'Loan',
  mixins: [Authenticated, DataRouteGuards, FormMixin, LoanStepsSequence, UserMixin],
  components: {
    LoanActions,
    LoanHeader,
    LoanMenu,
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
    loanForm() {
      return this.$store.state.loans.form;
    },
    pageLoaded() {
      return this.routeDataLoaded && this.item && this.loadedFullLoanable;
    },
    pageTitle() {
      return (this.item && this.item.loanable)
        ? this.item.loanable.name
        : capitalize(this.$i18n.tc('titles.loanable', 1));
    },
    userIsOwner() {
      if (!this.item.loanable.owner) {
        return false;
      }

      return this.user.id === this.item.loanable.owner.user.id;
    },
  },
  methods: {
    addExtension() {
      const handover = this.item.actions.find(a => a.type === 'handover');

      if (handover) {
        const indexOfHandover = this.item.actions.indexOf(handover);
        this.item.actions.splice(indexOfHandover, 0, {
          status: 'in_process',
          new_duration: this.item.actual_duration_in_minutes,
          comments_on_extension: '',
          type: 'extension',
          loan_id: this.item.id,
        });
      }

      setTimeout(() => {
        const el = document.getElementById('loan-extension-new');
        this.$scrollTo(el);
      }, 10);
    },
    addIncident(type) {
      const handover = this.item.actions.find(a => a.type === 'handover');

      if (handover) {
        const indexOfHandover = this.item.actions.indexOf(handover);
        this.item.actions.splice(indexOfHandover, 0, {
          status: 'in_process',
          incident_type: type,
          type: 'incident',
          loan_id: this.item.id,
        });
      }

      setTimeout(() => {
        const el = document.getElementById('loan-incident-new');
        this.$scrollTo(el);
      }, 10);
    },
    async cancelLoan() {
      await this.$store.dispatch('loans/cancel', this.item.id);
      await this.loadItemAndUser();
    },
    async formMixinCallback() {
      const { id, type } = this.item.loanable;
      await this.$store.dispatch(`${type}s/retrieveOne`, {
        params: {
          fields: '*,owner.id,owner.user.id,owner.user.avatar,owner.user.name,owner.user.phone,'
            + 'community.name',
          '!fields': 'events',
        },
        id,
      });
      const loanable = this.$store.state[`${type}s`].item;

      this.$store.commit(`${type}s/item`, null);

      this.$store.commit(`${this.slug}/mergeItem`, { loanable });

      this.loadedFullLoanable = true;
    },
    async loadItemAndUser() {
      this.loadedFullLoanable = false;

      await Promise.all([
        this.loadItem(),
        this.$store.dispatch('loadUser'),
      ]);
    },
    skipLoadItem() {
      if (this.id === 'new') {
        return true;
      }

      return false;
    },
    async submitLoan() {
      await this.submit();
      await this.loadItemAndUser();
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

  .loan-menu {
    margin-bottom: 30px;
  }
}
</style>
