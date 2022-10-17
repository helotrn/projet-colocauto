<template>
  <div class="profile-payment_methods" v-if="routeDataLoaded && loaded && !loading">
    <strong v-if="data.length > 0">Modes de paiements enregistrés:</strong>
    <ul class="profile-payment_methods__payment_methods">
      <li v-for="paymentMethod in data" :key="paymentMethod.id">
        <router-link :to="'/profile/payment_methods/' + paymentMethod.id">{{
          paymentMethod.name
        }}</router-link>
        <b-button
          size="sm"
          variant="outline-danger"
          class="ml-2"
          @click="() => destroy(paymentMethod.id)"
          >Supprimer</b-button
        >
      </li>
    </ul>
    <div v-if="data.length < 3">
      <b-button to="/profile/payment_methods/new"> Ajoutez un nouveau mode de paiement </b-button>
    </div>
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
    async destroy(id) {
      await this.$store.dispatch("paymentMethods/destroy", id);
      await this.$store.dispatch("paymentMethods/retrieve");
    },
  },
};
</script>
<style lang="scss">
.profile-payment_methods {
  &__payment_methods {
    list-style: none;
    padding-left: 0;
    li {
      margin-top: 0.5rem;
    }
  }
}
</style>
