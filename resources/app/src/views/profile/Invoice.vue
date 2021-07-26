<template>
  <div class="profile-invoice" fluid v-if="item && routeDataLoaded">
    <invoice-single :invoice="item" />
  </div>
  <layout-loading v-else />
</template>

<script>
import InvoiceSingle from "@/components/Invoice/Single.vue";

import Authenticated from "@/mixins/Authenticated";
import DataRouteGuards from "@/mixins/DataRouteGuards";
import FormMixin from "@/mixins/FormMixin";
import UserMixin from "@/mixins/UserMixin";

export default {
  name: "ProfileInvoice",
  mixins: [Authenticated, DataRouteGuards, FormMixin, UserMixin],
  components: { InvoiceSingle },
};
</script>

<style lang="scss"></style>
