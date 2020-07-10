<template>
  <div v-if="item && routeDataLoaded">
    <vue-headful :title="fullTitle" />

    <b-container fluid>

      <b-row>
        <b-col>
          <h1 v-if="item.period">{{ item.period }}</h1>
          <h1 v-else><em>{{ $tc('facture', 1) | capitalize }}</em></h1>
        </b-col>
      </b-row>

      <invoice-single :invoice="item" v-if="item.id" />
      <div v-else>
        <b-row>
          <b-col>
            <div>
              <b-row>
                <b-col sm="6">
                  <p class="text-left">
                    LocoMotion<br>
                    Solon collectif (Celsius Mtl)<br>
                    5985, rue St-Hubert<br>
                    Montréal, QC<br>
                    H2S 2L8
                  </p>
                </b-col>

                <b-col sm="6">
                  <p class="text-right">
                    {{ user.full_name }}<br>
                    {{ user.address }}<br>
                    {{ user.postal_code }}
                  </p>
                </b-col>
              </b-row>
            </div>
          </b-col>
        </b-row>

        <b-row class="mt-3 mb-3">
          <b-col>
            <forms-validated-input inline="auto" label="Titre" type="text"
              name="period" v-model="item.period" :rules="{ required: true }" />
          </b-col>
        </b-row>

        <b-row>
          <b-col>
            <b-table
              :items="item.bill_items" :fields="fields"
              no-local-sorting no-sort-reset>
              <template v-slot:cell(amount)="row">
                {{ row.item.amount | currency }}
              </template>
              <template v-slot:cell(actions)="row">
                <b-button size="sm" class="mr-1" variant="danger"
                  @click="removeBillItem(row.item)">
                  {{ $t('supprimer') | capitalize }}
                </b-button>
              </template>

              <template v-slot:custom-foot>
                <b-tr class="invoice-view__footer-row">
                  <b-td colspan="2">
                    Sous-total<br>
                    TPS<br>
                    TVQ<br>
                    Total
                  </b-td>
                  <b-td>
                    {{ itemTotal | currency }}<br>
                    {{ itemTotalTps | currency }}<br>
                    {{ itemTotalTvq | currency }}<br>
                    {{ itemTotalWithTaxes | currency }}
                  </b-td>
                </b-tr>
              </template>
            </b-table>
          </b-col>
        </b-row>

        <hr>

        <b-row>
          <b-col>
            <h3>Nouvel item</h3>

            <forms-validated-input label="Description" type="text"
              name="label" v-model="newBillItem.label" :rules="{ required: true }" />
            <forms-validated-input label="Date" type="date"
              name="item_date" v-model="newBillItem.item_date" :rules="{ required: true }" />
            <forms-validated-input label="Montant" type="number"
              name="amount" v-model="newBillItem.amount" :rules="{ required: true }" />
            <forms-validated-input label="TPS" type="number"
              name="taxes_tps" v-model="newBillItem.taxes_tps" :rules="{ required: true }" />
            <forms-validated-input label="TPS" type="number"
              name="taxes_tvq" v-model="newBillItem.taxes_tvq" :rules="{ required: true }" />

            <b-button @click="addBillItemAndReset">Ajouter l'item</b-button>
          </b-col>
        </b-row>

        <hr>

        <b-row>
          <b-col>
            <div class="form__buttons">
              <b-button-group>
                <b-button variant="success" type="submit"
                  @click="mergeUserAndSubmit" :disabled="!changed || loading">
                  Sauvegarder
                </b-button>
              </b-button-group>
            </div>
          </b-col>
        </b-row>
      </div>
    </b-container>
  </div>
  <layout-loading v-else />
</template>

<script>
import FormsValidatedInput from '@/components/Forms/ValidatedInput.vue';
import InvoiceSingle from '@/components/Invoice/Single.vue';

import DataRouteGuards from '@/mixins/DataRouteGuards';
import FormMixin from '@/mixins/FormMixin';

import locales from '@/locales';

import { capitalize } from '@/helpers/filters';

export default {
  name: 'AdminInvoice',
  mixins: [DataRouteGuards, FormMixin],
  components: {
    FormsValidatedInput,
    InvoiceSingle,
  },
  data() {
    return {
      fields: [
        { key: 'item_date', label: 'Date', sortable: false },
        { key: 'label', label: 'Description', sortable: false },
        { key: 'amount', label: 'Montant', sortable: false },
        { key: 'actions', label: 'Actions', tdClass: 'table__cell__actions' },
      ],
      newBillItem: {
        item_date: this.$dayjs().format('YYYY-MM-DD'),
        label: '',
        amount: 0,
        taxes_tps: 0,
        taxes_tvq: 0,
      },
    };
  },
  computed: {
    fullTitle() {
      const parts = [
        'LocoMotion',
        capitalize(this.$i18n.t('titles.admin')),
        capitalize(this.$i18n.tc('facture', 2)),
      ];

      if (this.pageTitle) {
        parts.push(this.pageTitle);
      }

      return parts.reverse().join(' | ');
    },
    itemTotal() {
      return this.item.bill_items
        .reduce((acc, i) => (parseFloat(i.amount, 10) + acc), 0);
    },
    itemTotalTps() {
      return this.item.bill_items
        .reduce((acc, i) => (parseFloat(i.taxes_tps, 10) + acc), 0);
    },
    itemTotalTvq() {
      return this.item.bill_items
        .reduce((acc, i) => (parseFloat(i.taxes_tvq, 10) + acc), 0);
    },
    itemTotalWithTaxes() {
      return this.itemTotal + this.itemTotalTps + this.itemTotalTvq;
    },
    newBillItemTotal() {
      const { amount, tps, tvq } = this.newBillItem;
      return parseFloat(amount, 10) + parseFloat(tps, 10) + parseFloat(tvq, 10);
    },
    pageTitle() {
      return this.item.name || capitalize(this.$i18n.tc('facture', 1));
    },
    user() {
      return this.$store.state.users.item;
    },
  },
  methods: {
    addBillItemAndReset() {
      this.item.bill_items.push(this.newBillItem);

      this.newBillItem = {
        item_date: this.$dayjs().format('YYYY-MM-DD'),
        label: '',
        amount: 0,
        taxes_tps: 0,
        taxes_tvq: 0,
      };
    },
    async mergeUserAndSubmit() {
      this.item.user_id = this.user.id;
      await this.submit();
    },
    removeBillItem(item) {
      const index = this.item.bill_items.indexOf(item);

      this.item.bill_items.splice(index, 1);
    },
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.invoices,
        ...locales.en.forms,
        titles: locales.en.titles,
      },
      fr: {
        ...locales.fr.invoices,
        ...locales.fr.forms,
        titles: locales.fr.titles,
      },
    },
  },
};
</script>

<style lang="scss">
</style>
