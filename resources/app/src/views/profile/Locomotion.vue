<template>
  <div class="profile-locomotion" v-if="item && routeDataLoaded">
    <profile-form
      :loading="loading"
      :user="item"
      :form="form"
      @reset="reset"
      :changed="changed"
      show-reset
      @submit="submit"
      v-if="item"
    >
      <template>
        <b-row v-if="hasAddressChanged">
          <b-col>
            <b-alert variant="danger" show class="address-change-warning">
              Si votre changement d'adresse entraine un changement de quartier, vous devrez
              soumettre une nouvelle preuve de résidence.
            </b-alert>
          </b-col>
        </b-row>
      </template>
    </profile-form>
  </div>
  <layout-loading v-else />
</template>

<script>
import ProfileForm from "@/components/Profile/ProfileForm.vue";

import DataRouteGuards from "@/mixins/DataRouteGuards";
import FormMixin from "@/mixins/FormMixin";

export default {
  name: "ProfileLocomotion",
  mixins: [DataRouteGuards, FormMixin],
  components: { ProfileForm },
  props: {
    id: {
      required: false,
      default: "me",
    },
  },
  computed: {
    hasAddressChanged() {
      return this.item.address !== this.initialItem.address;
    },
  },
};
</script>

<style lang="scss">
.profile-locomotion {
  margin-bottom: 3em;
  .address-change-warning {
    margin-top: 20px;
  }
}
</style>
