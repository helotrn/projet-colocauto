<template>
  <div class="register-step">
    <b-pagination-nav v-bind:value="currentPage"
      :number-of-pages="4" pills
      align="center" use-router
      :hide-goto-end-buttons="true"
      :disabled="true">
      <template v-slot:page="{ page, active }">
        <span v-if="page < currentPage" class="checked">
          <b-icon icon="check" font-scale="2" />
        </span>
        <span v-else>{{ page }}</span>
      </template>
    </b-pagination-nav>

    <div v-if="currentPage == 2" class="register-step__profile">
      <h2>Profil de membre</h2>

      <p class="register-step__profile__text">
        Remplissez vos informations de profil Locomotion.
      </p>

      <profile-form v-if="item" :user="item" :loading="loading" @submit="submitAndReload" />
    </div>
  </div>
</template>

<script>
import Authenticated from '@/mixins/Authenticated';
import Notification from '@/mixins/Notification';

import ProfileForm from '@/components/Profile/Form.vue';

import FormMixin from '@/mixins/FormMixin';

import helpers from '@/helpers';

const { extractErrors } = helpers;

export default {
  name: 'RegisterStep',
  mixins: [Authenticated, FormMixin, Notification],
  components: { ProfileForm },
  beforeRouteEnter(to, from, next) {
    next((vm) => {
      if (vm.isLoggedIn) {
        if (!vm.isRegistered) {
          if (vm.$route.path !== '/register/2') {
            return vm.$router.replace('/register/2');
          }

          return null;
        }

        if (vm.user.communities.length === 0) {
          if (vm.$route.path !== '/register/map') {
            return vm.$router.replace('/register/map');
          }

          return null;
        }

        return vm.$router.replace('/register/3');
      }

      if (vm.$route.path !== '/register/1') {
        return vm.$router.replace('/register/1');
      }

      return null;
    });
  },
  props: {
    id: {
      required: false,
      default: 'me',
    },
    step: {
      type: String,
      required: true,
    },
  },
  computed: {
    currentPage() {
      const stepIndex = parseInt(this.step, 10);

      if (Number.isNaN(stepIndex)) {
        return 1;
      }

      return stepIndex;
    },
  },
  methods: {
    async submitAndReload() {
      try {
        await this.submit();

        this.$store.commit('user', this.$store.state.users.item);

        this.$store.commit('addNotification', {
          content: 'Il est temps de choisir une première communauté!',
          title: 'Profil mis-à-jour',
          variant: 'success',
          type: 'register',
        });

        this.$router.push('/register/map');
      } catch (e) {
        if (e.request) {
          switch (e.request.status) {
            case 422:
            default:
              this.$store.commit('addNotification', {
                content: extractErrors(e.response.data).join(', '),
                title: "Erreur d'inscription",
                variant: 'danger',
                type: 'register',
              });
          }
        }
      }
    },
  },
};
</script>

<style lang="scss">
.register-step {
  background-color: $white;
  padding: 53px $grid-gutter-width / 2 45px;
  width: 590px;
  max-width: 100%;
  margin: 0 auto;

  .register-step__title {
    text-align: center;
  }

  h2 {
    text-align: center;
  }
}
</style>
