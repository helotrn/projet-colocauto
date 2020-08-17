<template>
  <div class="profile-locomotion" v-if="item">
    <profile-form :loading="loading" :user="item" :form="form"
      @reset="reset" :changed="changed" show-reset
      @submit="submit" v-if="item" />
  </div>
  <layout-loading v-else />
</template>

<script>
import ProfileForm from '@/components/Profile/ProfileForm.vue';

import FormMixin from '@/mixins/FormMixin';

export default {
  name: 'ProfileLocomotion',
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
.profile-locomotion {
  margin-bottom: 3em;
}
</style>
