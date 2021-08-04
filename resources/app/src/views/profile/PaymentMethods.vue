<template>
  <div class="profile-payment_methods" v-if="routeDataLoaded">
    <b-row>
      <b-col class="admin__buttons">
        <b-btn v-if="creatable" to="/profile/payment_methods/new">
          {{ $t("ajouter un mode de paiement") | capitalize }}
        </b-btn>
      </b-col>
    </b-row>

    <b-row v-if="data.length === 0">
      <p>
        Vous pouvez ajouter un mode de paiement pour accélérer l'ajout de crédits.
        <router-link to="/profile/payment_methods/new"> Cliquez ici </router-link> pour commencer!
      </p>
    </b-row>
    <b-row
      v-else
      v-for="paymentMethod in data"
      :key="paymentMethod.id"
      class="profile-payment_methods__payment_methods"
    >
      <b-col class="profile-payment_methods__payment_methods__payment_method">
        <router-link :to="`/profile/payment_methods/${paymentMethod.id}`">
          {{ paymentMethod.name }}
        </router-link>
      </b-col>
    </b-row>
  </div>
  <layout-loading v-else />
</template>

<script>
import Authenticated from "@/mixins/Authenticated";
import DataRouteGuards from "@/mixins/DataRouteGuards";
import ListMixin from "@/mixins/ListMixin";
import UserMixin from "@/mixins/UserMixin";

import locales from "@/locales";

export default {
  name: "ProfilePaymentMethods",
  mixins: [Authenticated, DataRouteGuards, ListMixin, UserMixin],
  data() {
    return {
      selected: [],
      fields: [
        { key: "id", label: "ID", sortable: true },
        { key: "name", label: "Nom", sortable: true },
        { key: "type", label: "Type", sortable: false },
        { key: "actions", label: "Actions", tdClass: "table__cell__actions" },
      ],
    };
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.loanables,
        ...locales.en.forms,
      },
      fr: {
        ...locales.fr.loanables,
        ...locales.fr.forms,
      },
    },
  },
  methods: {
    async createOwnerProfile() {
      this.$store.commit("users/item", {
        ...this.user,
        owner: {},
      });
      await this.$store.dispatch("users/updateItem");
      await this.$store.dispatch("loadUser");
    },
  },
};
</script>

<style lang="scss">
.profile-loanables__loanables__loanable {
  margin-bottom: 20px;
}
</style>
