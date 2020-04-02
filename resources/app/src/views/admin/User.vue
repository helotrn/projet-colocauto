<template>
  <b-container fluid v-if="item">
    <b-row>
      <b-col>
        <h1 v-if="item.name">{{ item.name }}</h1>
        <h1 v-else><em>{{ $tc('membre', 1) | capitalize }}</em></h1>
      </b-col>
    </b-row>

    <b-row>
      <b-col>
        <b-form class="form" @submit.prevent="submit">
          <div class="form__section">
            <h2>Informations générales</h2>

            <forms-builder :definition="form.general" :item="item" entity="users" />
          </div>

          <div class="form__section">
            <h2>Profil d'emprunteur</h2>

            <p>
              <em>Un profil d'emprunteur valide est requis pour emprunter une voiture.</em>
            </p>

            <p>
              <strong>Statut:</strong>
              {{ borrowerStatus }}

              <b-button v-if="!item.borrower.approved_at"
                size="sm" class="ml-1" variant="primary"
                :disabled="loading"
                @click="approveBorrower(item)">
                {{ $t('approuver') | capitalize }}
              </b-button>
              <b-button v-else-if="!item.borrower.suspended_at"
                size="sm" class="ml-1" variant="warning"
                :disabled="loading"
                @click="suspendBorrower(item)">
                {{ $t('suspendre') | capitalize }}
              </b-button>
              <b-button v-else
                size="sm" class="mr-1" variant="success"
                :disabled="loading"
                @click="unsuspendBorrower(item)">
                {{ $t('rétablir') | capitalize }}
              </b-button>
            </p>

            <forms-builder :definition="form.borrower" :item="item.borrower"
              entity="borrowers" />
          </div>

          <div class="form__section" v-if="!!item.id">
            <h2>Véhicules</h2>

            <b-table
              striped hover :items="item.loanables"
              selectable select-mode="multi" @row-selected="loanableRowSelected"
              :fields="loanablesTable" no-sort-reset
              :show-empty="true" empty-text="Pas de véhicules">
              <template v-slot:cell(actions)="row">
                <div class="text-right">
                  <b-button size="sm" :to="`/admin/loanables/${row.item.id}`">
                    {{ $t('modifier') | capitalize }}
                  </b-button>
                </div>
              </template>
            </b-table>
          </div>

          <div class="form__section" v-if="!!item.id">
            <h2>Communautés</h2>

            <b-table
              striped hover :items="item.communities"
              selectable select-mode="multi" @row-selected="communityRowSelected"
              :fields="communitiesTable" no-sort-reset
              :show-empty="true" empty-text="Pas de communauté">
              <template v-slot:cell(actions)="row">
                <div class="text-right">
                  <b-button size="sm" :to="`/admin/communities/${row.item.id}#members`">
                    {{ $t('modifier') | capitalize }}
                  </b-button>
                </div>
              </template>
            </b-table>
          </div>

          <div class="form__section" v-if="!!item.id">
            <h2>Emprunts</h2>

            <b-table
              striped hover :items="allLoans"
              selectable select-mode="multi" @row-selected="loanRowSelected"
              :fields="loansTable" no-sort-reset
              :show-empty="true" empty-text="Pas d'emprunt">
              <template v-slot:cell(borrower)="row">
                {{ row.item.borrower.user.full_name }}
              </template>
              <template v-slot:cell(loanable)="row">
                {{ row.item.loanable.name }}
              </template>
              <template v-slot:cell(date)="row">
                <span v-if="isLoanMultipleDays(row.item)">
                  {{ row.item.departure_at | date }} {{ row.item.departure_at | time }}<br>
                  {{ computeReturnAt(row.item) | date }} {{ computeReturnAt(row.item) | time }}
                </span>
                <span v-else>
                  {{ row.item.departure_at | date }}<br>
                  {{ row.item.departure_at | time }} à {{ computeReturnAt(row.item) | time }}
                </span>
              </template>
              <template v-slot:cell(actions)="row">
                <div class="text-right">
                  <b-button size="sm" :to="`/admin/loans/${row.item.id}`">
                    {{ $t('modifier') | capitalize }}
                  </b-button>
                </div>
              </template>
            </b-table>
          </div>

          <div class="form__section" v-if="!!item.id">
            <h2>Compte</h2>

            <p><strong>Balance:</strong> {{ roundedBalance | currency }}</p>

            <p><strong>Transactions</strong></p>

            <b-table
              striped hover :items="item.transactions"
              selectable select-mode="multi" @row-selected="transactionRowSelected"
              :fields="transactionsTable" no-sort-reset
              :show-empty="true" empty-text="Pas de transaction">
              <template v-slot:cell(actions)="row">
                <div class="text-right">
                  <b-button size="sm" :to="`/admin/invoices/${row.item.id}`">
                    {{ $t('modifier') | capitalize }}
                  </b-button>
                </div>
              </template>
            </b-table>

            <p><strong>Factures</strong></p>

            <b-table
              striped hover :items="item.invoices"
              selectable select-mode="multi" @row-selected="invoiceRowSelected"
              :fields="invoicesTable" no-sort-reset
              :show-empty="true" empty-text="Pas de facture">
              <template v-slot:cell(paid_at)="row">
                {{ row.item.paid_at ? '✓' : '✗' }}
              </template>
              <template v-slot:cell(created_at)="row">
                {{ row.item.created_at | date }}
              </template>
              <template v-slot:cell(total)="row">
                {{ row.item.total | currency }}
              </template>
              <template v-slot:cell(total_with_taxes)="row">
                {{ row.item.total_with_taxes | currency }}
              </template>
              <template v-slot:cell(actions)="row">
                <div class="text-right">
                  <b-button size="sm" :to="`/admin/invoices/${row.item.id}`">
                    {{ $t('modifier') | capitalize }}
                  </b-button>
                </div>
              </template>
            </b-table>
          </div>

          <div class="form__buttons">
            <b-button-group>
              <b-button variant="success" type="submit" :disabled="!changed || loading">
                Sauvegarder
              </b-button>
              <b-button type="reset" :disabled="!changed" @click="reset">
                Réinitialiser
              </b-button>
            </b-button-group>
          </div>
        </b-form>
      </b-col>
    </b-row>
  </b-container>
  <layout-loading v-else />
</template>

<script>
import FormsBuilder from '@/components/Forms/Builder.vue';

import FormMixin from '@/mixins/FormMixin';

import locales from '@/locales';

export default {
  name: 'AdminUser',
  mixins: [FormMixin],
  components: {
    FormsBuilder,
  },
  data() {
    return {
      communitiesSelected: [],
      communitiesTable: [
        { key: 'id', label: 'ID', sortable: true },
        { key: 'name', label: 'Nom', sortable: true },
        { key: 'actions', label: 'Actions', tdClass: 'table__cell__actions' },
      ],
      invoicesSelected: [],
      invoicesTable: [
        { key: 'created_at', label: 'Date', sortable: true },
        { key: 'paid_at', label: 'Payée', sortable: true },
        {
          key: 'total',
          label: 'Total',
          sortable: true,
          tdClass: 'text-right',
        },
        {
          key: 'total_with_taxes',
          label: 'Total avec taxes',
          sortable: true,
          tdClass: 'text-right',
        },
        { key: 'actions', label: 'Actions', tdClass: 'table__cell__actions' },
      ],
      loanablesSelected: [],
      loanablesTable: [
        { key: 'id', label: 'ID', sortable: true },
        { key: 'name', label: 'Nom', sortable: true },
        { key: 'actions', label: 'Actions', tdClass: 'table__cell__actions' },
      ],
      loansSelected: [],
      loansTable: [
        { key: 'id', label: 'ID', sortable: true },
        { key: 'borrower', label: 'Emprunteur', sortable: true },
        { key: 'loanable', label: 'Objet', sortable: true },
        { key: 'date', label: 'Date', sortable: true },
        { key: 'actions', label: 'Actions', tdClass: 'table__cell__actions' },
      ],
      transactionsTable: [
        { key: 'id', label: 'ID', sortable: true },
        { key: 'actions', label: 'Actions', tdClass: 'table__cell__actions' },
      ],
    };
  },
  computed: {
    allLoans() {
      return [
        ...this.item.loanables.reduce((acc, loanable) => {
          acc.push(
            ...loanable.loans.map(l => ({
              ...l,
              loanable: {
                ...loanable,
                owner: {
                  ...this.item.owner,
                  user: this.item,
                },
              },
            })),
          );
          return acc;
        }, []),
        ...this.item.loans,
      ];
    },
    borrowerStatus() {
      if (!this.item.borrower.approved) {
        return 'Non approuvé';
      }

      return this.item.borrower.suspended ? 'Suspendu' : 'Approuvé';
    },
    roundedBalance() {
      if (this.item.balance) {
        return Math.floor(this.item.balance);
      }

      return 0;
    },
  },
  methods: {
    async approveBorrower(user) {
      await this.$store.dispatch(`${this.slug}/approveBorrower`, user.id);
    },
    communityRowSelected(rows) {
      this.communitiesSelected = rows;
    },
    computeReturnAt(loan) {
      return this.$dayjs(loan.departure_at)
        .add(loan.duration_in_minutes, 'minute')
        .format('YYYY-MM-DD HH:mm:ss');
    },
    invoiceRowSelected(rows) {
      this.invoicesSelected = rows;
    },
    isLoanMultipleDays(loan) {
      return this.$dayjs(loan.departure_at).format('YYYY-MM-DD')
        !== this.$dayjs(this.computeReturnAt(loan)).format('YYYY-MM-DD');
    },
    loanableRowSelected(rows) {
      this.loanablesSelected = rows;
    },
    loanRowSelected(rows) {
      this.loansSelected = rows;
    },
    async suspendBorrower(user) {
      await this.$store.dispatch(`${this.slug}/suspendBorrower`, user.id);
    },
    async unsuspendBorrower(user) {
      await this.$store.dispatch(`${this.slug}/unsuspendBorrower`, user.id);
    },
    transactionRowSelected(rows) {
      this.transactionsSelected = rows;
    },
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.users,
        ...locales.en.forms,
      },
      fr: {
        ...locales.fr.users,
        ...locales.fr.forms,
      },
    },
  },
};
</script>

<style lang="scss">
</style>
