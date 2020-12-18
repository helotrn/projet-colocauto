<template>
  <div class="profile-locomotion" v-if="item && routeDataLoaded">
    <profile-form :loading="loading" :user="item" :form="form"
      @reset="reset" :changed="changed" show-reset
      @submit="submit" v-if="item">
      <template>
        <b-row v-if="hasAddressChanged">
          <b-col>
            <b-alert variant="warning" show>
              Si vous avez changé d'adresse et souhaitez rejoiundre une nouvelle communauté,
              merci de prendre contact avec un administrateur à l'adresse
              <a href="mailto:info@locomotion.app">info@locomotion.app</a> .
            </b-alert>
          </b-col>
        </b-row>
      </template>
    </profile-form>
  </div>
  <layout-loading v-else />
</template>

<script>
import ProfileForm from '@/components/Profile/ProfileForm.vue';

import DataRouteGuards from '@/mixins/DataRouteGuards';
import FormMixin from '@/mixins/FormMixin';

export default {
  name: 'ProfileLocomotion',
  mixins: [DataRouteGuards, FormMixin],
  components: { ProfileForm },
  props: {
    id: {
      required: false,
      default: 'me',
    },
  },
  computed: {
    hasAddressChanged() {
      return this.item.address !== this.parsedInitialItem.address
        || this.item.postal_code !== this.parsedInitialItem.postal_code;
    },
  },
};
</script>

<style lang="scss">
.profile-locomotion {
  margin-bottom: 3em;
}
</style>
