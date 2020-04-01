<template>
  <b-container class="invoice-view" fluid>
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
          {{ invoice.user.full_name }}<br>
          {{ invoice.user.address }}<br>
          {{ invoice.user.postal_code }}
        </p>
      </b-col>
    </b-row>

    <b-row>
      <b-col>
        <b-table
          :items="invoice.bill_items" :fields="fields"
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
                {{ invoice.total | currency }}<br>
                {{ invoice.tps | currency }}<br>
                {{ invoice.tvq | currency }}<br>
                {{ invoice.total_with_taxes | currency }}
              </b-td>
            </b-tr>
          </template>
        </b-table>
      </b-col>
    </b-row>
  </b-container>
</template>

<script>
import locales from '@/locales';

export default {
  name: 'View',
  props: {
    invoice: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      fields: [
        { key: 'item_date', label: 'Date', sortable: false },
        { key: 'label', label: 'Description', sortable: false },
        { key: 'amount', label: 'Montant', sortable: false },
      ],
    };
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
.invoice-view {
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
