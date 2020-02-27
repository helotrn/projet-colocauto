<template>
  <div class="profile-account" v-if="item">
    <b-tabs content-class="mt-3" fill no-fade @activate-tab="checkChanges">
      <b-tab title="Profil" class="profile-account__profile">
        <profile-form :loading="loading" :user="item"
          @reset="reset" :changed="changed" show-reset
          @submit="submit" v-if="item" />
      </b-tab>

      <b-tab title="Emprunteur" class="profile-account__borrower" v-if="item.borrower">
        <borrower-form :loading="loading" :borrower="item.borrower"
          @reset="reset" :changed="changed" show-reset
          @submit="submit" v-if="item" />
      </b-tab>
    </b-tabs>
  </div>
</template>

<script>
import BorrowerForm from '@/components/Borrower/Form.vue';
import ProfileForm from '@/components/Profile/Form.vue';

import FormMixin from '@/mixins/FormMixin';

export default {
  name: 'ProfileAccount',
  mixins: [FormMixin],
  components: { BorrowerForm, ProfileForm },
  props: {
    id: {
      required: false,
      default: 'me',
    },
  },
  methods: {
    checkChanges(newIndex, prevIndex, event) {
      if (this.changed) {
        this.$store.commit('addNotification', {
          content: 'Vous avez des changements non enregistrés.',
          title: 'Navigation impossible',
          variant: 'warning',
          type: 'save',
        });

        event.preventDefault();
      }
    },
  },
};
</script>

<style lang="scss">
.profile-account {
  .profile-form, .borrower-form {
    margin-bottom: 60px;
  }
}
</style>
