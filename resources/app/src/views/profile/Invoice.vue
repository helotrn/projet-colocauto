<template>
  <div class="profile-invoice" fluid v-if="item">
    <invoice-view :invoice="item" />
  </div>
  <layout-loading v-else />
</template>

<script>
import InvoiceView from '@/components/Invoice/View.vue';

import Authenticated from '@/mixins/Authenticated';
import FormMixin from '@/mixins/FormMixin';

export default {
  name: 'ProfileInvoice',
  mixins: [Authenticated, FormMixin],
  components: { InvoiceView },
};
</script>

<style lang="scss">
</style>
