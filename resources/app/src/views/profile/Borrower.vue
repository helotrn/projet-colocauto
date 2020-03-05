<template>
  <div class="profile-borrower" v-if="item">
    <borrower-form :loading="loading" :borrower="item.borrower"
      @reset="reset" :changed="changed" show-reset
      @submit="submit" v-if="item" />
  </div>
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
