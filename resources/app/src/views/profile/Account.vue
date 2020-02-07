<template>
  <div class="profile-account">
    <profile-form :loading="loading" :user="item"
      @reset="reset" :changed="changed" show-reset
      @submit="submit" v-if="item" />
  </div>
</template>

<script>
import ProfileForm from '@/components/Profile/Form.vue';

import FormMixin from '@/mixins/FormMixin';

export default {
  name: 'ProfileAccount',
  mixins: [FormMixin],
  components: { ProfileForm },
  props: {
    id: {
      required: false,
      default: 'me',
    },
  },
};
</script>

<style lang="scss">
.profile-account {
  .profile-form {
    margin-bottom: 60px;
  }
}
</style>
