<template>
  <div class="waller-refunds">
    <b-row>
      <b-col class="admin__buttons">
        <b-btn v-if="creatable" :to="`/wallet/${slug}/new`">
          {{ $t("ajouter un remboursement") | capitalize }}
        </b-btn>
      </b-col>
    </b-row>

    <b-row>
      <b-col>
        <refund-filters />
      </b-col>
    </b-row>

    <div v-if="routeDataLoaded && !loading && loaded">
      <b-row v-if="data.length === 0">
        <b-col>Pas de remboursement.</b-col>
      </b-row>
      <b-row
        v-else
        v-for="refund in data"
        :key="refund.id"
        class="wallet-refunds__refunds"
      >
        <b-col class="wallet-refunds__refunds__refund">
          <refund-info-box v-bind="refund"></refund-info-box>
        </b-col>
      </b-row>

      <b-row v-if="lastPage > 1">
        <b-col>
          <b-pagination
            align="right"
            v-model="contextParams.page"
            :total-rows="total"
            :per-page="contextParams.per_page"
          />
        </b-col>
      </b-row>
    </div>
    <layout-loading v-else />
  </div>
</template>

<script>
import RefundInfoBox from "@/components/Refunds/InfoBox.vue";
import RefundFilters from "@/components/Refunds/Filters.vue";

import Authenticated from "@/mixins/Authenticated";
import DataRouteGuards from "@/mixins/DataRouteGuards";
import ListMixin from "@/mixins/ListMixin";
import UserMixin from "@/mixins/UserMixin";

import { extractErrors } from "@/helpers";
import locales from "@/locales";

export default {
  name: "WalletRefunds",
  mixins: [Authenticated, DataRouteGuards, ListMixin, UserMixin],
  components: { RefundInfoBox, RefundFilters },
  i18n: {
    messages: {
      en: {
        ...locales.en.refunds,
        ...locales.en.forms,
      },
      fr: {
        ...locales.fr.refunds,
        ...locales.fr.forms,
      },
    },
  },
};
</script>
