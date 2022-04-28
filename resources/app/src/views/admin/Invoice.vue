<template>
  <div v-if="item && routeDataLoaded">
    <vue-headful :title="fullTitle" />

    <b-container fluid>
      <b-row>
        <b-col>
          <h1 v-if="item.period">{{ item.period }}</h1>
          <h1 v-else>
            <em>{{ $tc("facture", 1) | capitalize }}</em>
          </h1>
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
                    LocoMotion<br />
                    Solon collectif (Celsius Mtl)<br />
                    5985, rue St-Hubert<br />
                    Montréal, QC<br />
                    H2S 2L8
                  </p>
                </b-col>

                <b-col sm="6">
                  <p class="text-right">
                    {{ user.full_name }}<br />
                    {{ user.address }}<br />
                    {{ user.postal_code }}
                  </p>
                </b-col>
              </b-row>
            </div>
          </b-col>
        </b-row>

        <b-row class="mt-3 mb-3">
          <b-col>
            <forms-validated-input
              inline="auto"
              label="Titre"
              type="text"
              name="period"
              v-model="item.period"
              :rules="{ required: true }"
            />
          </b-col>
        </b-row>

        <b-row>
          <b-col>
            <b-table :items="item.bill_items" :fields="fields" no-local-sorting no-sort-reset>
              <template v-slot:cell(amount)="row">
                {{ row.item.amount | currency }}
              </template>
              <template v-slot:cell(actions)="row">
                <b-button size="sm" class="mr-1" variant="danger" @click="removeBillItem(row.item)">
                  {{ $t("supprimer") | capitalize }}
                </b-button>
              </template>

              <template v-slot:custom-foot>
                <b-tr>
                  <b-td colspan="4">
                    <b-button
                      block
                      variant="light"
                      size="sm"
                      @click="createNewBillItem"
                      v-if="!newBillItem"
                    >
                      Nouvel item
                    </b-button>
                    <b-card id="new-item" v-else>
                      <validation-observer ref="observer" v-slot="{ passes }">
                        <b-form class="form" @submit.prevent="passes(addBillItemAndReset)">
                          <forms-validated-input
                            label="Description"
                            type="text"
                            name="label"
                            v-model="newBillItem.label"
                            :rules="{ required: true }"
                          />
                          <forms-validated-input
                            label="Date"
                            type="date"
                            name="item_date"
                            v-model="newBillItem.item_date"
                            :rules="{ required: true }"
                          />
                          <forms-validated-input
                            label="Montant"
                            type="number"
                            :step="0.01"
                            name="amount"
                            v-model="newBillItem.amount"
                            :rules="{ required: true }"
                          />
                          <!-- determine the type of the amount, which can be :
                              - debit: a payment that will be deducted from the user balance
                              - credit: add funds to the user balance
                          -->
                          <forms-validated-input
                            label="Type du montant"
                            type="select"
                            :options="amountTypes"
                            :rules="{ required: true }"
                            v-model="newBillItem.amount_type"
                          />
                          <forms-validated-input
                            label="TPS"
                            type="number"
                            :step="0.01"
                            name="taxes_tps"
                            v-model="newBillItem.taxes_tps"
                            :rules="{ required: true }"
                          />
                          <forms-validated-input
                            label="TPS"
                            type="number"
                            :step="0.01"
                            name="taxes_tvq"
                            v-model="newBillItem.taxes_tvq"
                            :rules="{ required: true }"
                          />

                          <div class="form__buttons">
                            <b-button-group>
                              <b-button type="submit">Ajouter l'item</b-button>
                              <b-button variant="warning" @click="newBillItem = null">
                                Annuler
                              </b-button>
                            </b-button-group>
                          </div>
                        </b-form>
                      </validation-observer>
                    </b-card>
                  </b-td>
                </b-tr>

                <b-tr class="invoice-view__footer-row">
                  <b-td colspan="3">
                    Sous-total<br />
                    TPS<br />
                    TVQ<br />
                    Total
                  </b-td>
                  <b-td class="text-right tabular-nums">
                    {{ itemTotal | currency }}<br />
                    {{ itemTotalTps | currency }}<br />
                    {{ itemTotalTvq | currency }}<br />
                    {{ itemTotalWithTaxes | currency }}
                  </b-td>
                </b-tr>
              </template>
            </b-table>
          </b-col>
        </b-row>

        <b-row>
          <b-col>
            <forms-validated-input
              label="Payée"
              type="checkbox"
              :value="true"
              name="paid_at"
              disabled
              description="Les factures créées manuellement sont présumées déjà payées."
            />

            <forms-validated-input
              label="Appliquer au solde"
              type="checkbox"
              name="apply_to_balance"
              v-model="item.apply_to_balance"
            />
          </b-col>
        </b-row>

        <b-row>
          <b-col />
        </b-row>

        <b-row>
          <b-col>
            <div class="form__buttons mt-3">
              <b-button-group>
                <b-button
                  variant="success"
                  type="submit"
                  @click="mergeUserAndSubmit"
                  :disabled="!changed || loading"
                >
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
import FormsValidatedInput from "@/components/Forms/ValidatedInput.vue";
import InvoiceSingle from "@/components/Invoice/Single.vue";

import DataRouteGuards from "@/mixins/DataRouteGuards";
import FormMixin from "@/mixins/FormMixin";

import locales from "@/locales";

import { capitalize } from "@/helpers/filters";

export default {
  name: "AdminInvoice",
  mixins: [DataRouteGuards, FormMixin],
  components: {
    FormsValidatedInput,
    InvoiceSingle,
  },
  data() {
    return {
      fields: [
        { key: "item_date", label: "Date", sortable: false, tdClass: "tabular-nums" },
        { key: "label", label: "Description", sortable: false },
        { key: "amount", label: "Montant", sortable: false, tdClass: "text-right tabular-nums" },
        { key: "actions", label: "Actions", tdClass: "table__cell__actions" },
      ],
      newBillItem: null,
      newBillItemTemplate: {
        item_date: this.$dayjs().format("YYYY-MM-DD"),
        label: "",
        amount: 0,
        amount_type: null,
        taxes_tps: 0,
        taxes_tvq: 0,
      },
    };
  },
  computed: {
    amountTypes() {
      return [
        { value: "debit", text: "Débit" },
        { value: "credit", text: "Crédit" },
      ];
    },
    fullTitle() {
      const parts = [
        "LocoMotion",
        capitalize(this.$i18n.t("titles.admin")),
        capitalize(this.$i18n.tc("facture", 2)),
      ];

      if (this.pageTitle) {
        parts.push(this.pageTitle);
      }

      return parts.reverse().join(" | ");
    },
    itemTotal() {
      return this.item.bill_items.reduce((acc, i) => parseFloat(i.amount, 10) + acc, 0);
    },
    itemTotalTps() {
      return this.item.bill_items.reduce((acc, i) => parseFloat(i.taxes_tps, 10) + acc, 0);
    },
    itemTotalTvq() {
      return this.item.bill_items.reduce((acc, i) => parseFloat(i.taxes_tvq, 10) + acc, 0);
    },
    itemTotalWithTaxes() {
      return this.itemTotal + this.itemTotalTps + this.itemTotalTvq;
    },
    newBillItemTotal() {
      const { amount, tps, tvq } = this.newBillItem;
      return parseFloat(amount, 10) + parseFloat(tps, 10) + parseFloat(tvq, 10);
    },
    pageTitle() {
      return this.item.name || capitalize(this.$i18n.tc("facture", 1));
    },
    user() {
      return this.$store.state.users.item;
    },
  },
  methods: {
    addBillItemAndReset() {
      // adjust the amount according to the type (only negate the amount if the type is a debit,
      // since it has to be substracted from the user balance). No need to update the amount if it
      // is already negative.
      if (this.newBillItem.amount > 0 && this.newBillItem.amount_type === "debit")
        this.newBillItem.amount *= -1;

      // add the bill item to invoice
      this.item.bill_items.push(this.newBillItem);

      // reset the bill item
      this.newBillItem = null;
    },
    createNewBillItem() {
      this.newBillItem = { ...this.newBillItemTemplate };
    },
    async mergeUserAndSubmit() {
      this.item.user_id = this.user.id;

      // Set the type of the invoice:
      // - negative amount: debit
      // - positive amount: credit
      if(this.itemTotalWithTaxes >= 0)
        this.item.type = "credit";
      else
        this.item.type = "debit";

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

<style lang="scss"></style>
