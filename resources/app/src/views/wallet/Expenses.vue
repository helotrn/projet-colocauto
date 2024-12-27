<template>
  <div class="wallet-expenses">
    <b-row>
      <b-col class="admin__buttons">
        <b-btn v-if="creatable" :to="`/wallet/${slug}/new`">
          {{ $t("ajouter une dépense") | capitalize }}
        </b-btn>
      </b-col>
    </b-row>

    <expense-filters />

    <div v-if="routeDataLoaded && !loading && loaded">
      <b-row v-if="data.length === 0">
        <b-col>Pas de dépenses.</b-col>
      </b-row>
      <b-row
        v-else
        v-for="expense in currentCommunityExpenses"
        :key="expense.id"
        class="wallet-expenses__expenses"
      >
        <b-col class="wallet-expenses__expenses__expense">
          <expense-info-box v-bind="expense"></expense-info-box>
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
import ExpenseInfoBox from "@/components/Expenses/InfoBox.vue";
import ExpenseFilters from "@/components/Expenses/Filters.vue";

import Authenticated from "@/mixins/Authenticated";
import DataRouteGuards from "@/mixins/DataRouteGuards";
import ListMixin from "@/mixins/ListMixin";
import UserMixin from "@/mixins/UserMixin";

import { extractErrors } from "@/helpers";
import locales from "@/locales";

export default {
  name: "WalletExpenses",
  mixins: [Authenticated, DataRouteGuards, ListMixin, UserMixin],
  components: { ExpenseInfoBox, ExpenseFilters },
  mounted(){
    this.contextParams.page = 1;
    this.contextParams['loanable.community.id'] = this.currentCommunity
  },
  computed: {
    currentCommunity() {
      return this.$store.state.communities.current
        ? this.$store.state.communities.current
        : this.user.main_community.id;
    },
    currentCommunityExpenses() {
      return this.data.filter(e => e.community.id == this.currentCommunity)
    }
  },
  watch:{
    currentCommunity(){
      // reload data when community change
      this.contextParams.page = 1;
      this.contextParams['loanable.community.id'] = this.currentCommunity
      this.loadListData()
    }
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.expenses,
        ...locales.en.forms,
      },
      fr: {
        ...locales.fr.expenses,
        ...locales.fr.forms,
      },
    },
  },
};
</script>

<style lang="scss" scoped>
  
</style>
