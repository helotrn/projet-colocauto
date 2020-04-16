<template>
  <div class="profile-account" v-if="item">
    <profile-form :loading="loading" :user="item" :form="form"
      @reset="reset" :changed="changed" show-reset
      @submit="submit" v-if="item" />
  </div>
  <layout-loading v-else />
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
  margin-bottom: 3em;
}
</style>
