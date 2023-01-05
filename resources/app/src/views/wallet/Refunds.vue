<template>
  <div class="waller-refunds" v-if="routeDataLoaded && !loading && loaded">
  	refunds list
  </div>
  <layout-loading v-else />
</template>

<script>
import Authenticated from "@/mixins/Authenticated";
import DataRouteGuards from "@/mixins/DataRouteGuards";
import ListMixin from "@/mixins/ListMixin";
import UserMixin from "@/mixins/UserMixin";

import { extractErrors } from "@/helpers";
import locales from "@/locales";

export default {
  name: "WalletRefunds",
  mixins: [Authenticated, DataRouteGuards, ListMixin, UserMixin],
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
