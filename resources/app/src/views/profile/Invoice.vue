<template>
  <div class="profile-invoice" fluid v-if="item">
    <invoice-single :invoice="item" />
  </div>
  <layout-loading v-else />
</template>

<script>
import InvoiceSingle from '@/components/Invoice/Single.vue';

import Authenticated from '@/mixins/Authenticated';
import FormMixin from '@/mixins/FormMixin';

export default {
  name: 'ProfileInvoice',
  mixins: [Authenticated, FormMixin],
  components: { InvoiceSingle },
};
</script>

<style lang="scss">
</style>
