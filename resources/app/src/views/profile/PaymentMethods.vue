<template>
  <div class="profile-payment_methods" v-if="routeDataLoaded && loaded && !loading">
    <template v-if="available">
    <strong v-if="data.length > 0">{{ $t("saved_payment_methods") }}</strong>
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
        >
          {{ $t("forms.supprimer") | capitalize }}</b-button
        >
      </li>
    </ul>
    <div v-if="data.length < 3">
      <b-button to="/profile/payment_methods/new"> {{ $t("add_new") }}</b-button>
    </div>
    </template>
    <template v-else>
      <em>{{ $tc("model_name", 2) }} {{ $t("indisponibles") }}</em>
    </template>
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
  computed: {
    available() {
      return process.env.VUE_APP_STRIPE_KEY != undefined && process.env.VUE_APP_STRIPE_KEY !== ''
    }
  },
  i18n: {
    messages: {
      en: {
        ...locales.en.paymentMethods,
      },
      fr: {
        ...locales.fr.paymentMethods,
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
