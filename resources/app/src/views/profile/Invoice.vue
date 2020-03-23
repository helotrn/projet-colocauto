<template>
  <b-container class="profile-invoice" fluid v-if="item">
    <b-row>
      <b-col>
        <h1>{{ item.name }}</h1>
      </b-col>
    </b-row>

    <b-row class="mb-5">
      <b-col lg="6">
        <p class="text-left">
          LocoMotion<br>
          Solon collectif (Celsius Mtl)<br>
          5985, rue St-Hubert<br>
          Montréal, QC<br>
          H2S 2L8
        </p>
      </b-col>

      <b-col lg="6">
        <p class="text-right">
          {{ item.user.full_name }}<br>
          {{ item.user.address }}<br>
          {{ item.user.postal_code }}
        </p>
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

          <template v-slot:custom-foot>
            <b-tr class="profile-invoice__footer-row">
              <b-td colspan="2">
                Sous-total<br>
                TPS<br>
                TVQ<br>
                Total
              </b-td>
              <b-td>
                {{ item.total | currency }}<br>
                {{ item.tps | currency }}<br>
                {{ item.tvq | currency }}<br>
                {{ totalWithTaxes | currency }}
              </b-td>
            </b-tr>
          </template>
        </b-table>
      </b-col>
    </b-row>
  </b-container>
  <layout-loading v-else />
</template>

<script>
import Authenticated from '@/mixins/Authenticated';
import FormMixin from '@/mixins/FormMixin';

import locales from '@/locales';

export default {
  name: 'ProfileInvoice',
  mixins: [Authenticated, FormMixin],
  data() {
    return {
      fields: [
        { key: 'item_date', label: 'Date', sortable: false },
        { key: 'label', label: 'Description', sortable: false },
        { key: 'amount', label: 'Montant', sortable: false },
      ],
    };
  },
  computed: {
    totalWithTaxes() {
      return parseFloat(this.item.total, 10)
        + parseFloat(this.item.tvq, 10)
        + parseFloat(this.item.tps, 10);
    },
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.invoices,
        ...locales.en.forms,
      },
      fr: {
        ...locales.fr.invoices,
        ...locales.fr.forms,
      },
    },
  },
};
</script>

<style lang="scss">
.profile-invoice {
  margin-bottom: 20px;

  &__footer-row {
    td:nth-child(1) {
      text-align: right;
      font-weight: bold;
    }

    td:nth-child(2) {
      text-style: italic;
    }
  }
}
</style>
