<template>
  <div class="profile-borrower" v-if="item">
    <b-alert variant="warning" show>
      Les informations ci-contre ne sont requises que si vous désirez emprunter des voitures.
    </b-alert>

    <borrower-form :loading="loading" :borrower="item.borrower"
      @reset="reset" :changed="changed" show-reset
      @submit="submit" v-if="item" />
  </div>
  <layout-loading v-else />
</template>

<script>
import BorrowerForm from '@/components/Borrower/Form.vue';

import FormMixin from '@/mixins/FormMixin';

export default {
  name: 'ProfileBorrower',
  mixins: [FormMixin],
  components: { BorrowerForm },
  props: {
    id: {
      required: false,
      default: 'me',
    },
  },
};
</script>

<style lang="scss">
</style>
