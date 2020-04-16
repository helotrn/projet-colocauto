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

      <profile-form v-if="item"
        :form="form" :user="item" :loading="loading"
        @submit="submitAndReload" />
      <layout-loading v-else />
    </div>

    <div v-if="currentPage == 3" class="register-step__community">
      <h2>Preuve de résidence</h2>

      <p class="register-step__community__text">
        Pour rejoindre une communauté LocoMotion, vous devez fournir une preuve de résidence.
      </p>

      <div v-if="item && item.communities">
        <community-proof-form v-for="community in item.communities"
          :key="community.id" :community="community" :loading="!hasAllProofs"
          @submit="submitCommunityProof" />
      </div>
      <layout-loading v-else />
    </div>

    <div v-if="currentPage == 4" class="register-step__intents">
      <h2>Utilisation désirée</h2>

      <div class="register-step__intents__text">
        <p>
          Vous avez presque terminé! Indiquez ci-dessous les fonctionnalités de la
          plateforme que vous désirez utiliser. Ceci vous permettra d'accéder plus
          rapidement au partage de voiture, par exemple.
        </p>

        <p>Vous pouvez aussi passer cette étape pour utiliser le partage de vélos.</p>
      </div>

      <register-intent-form :user="item" v-if="item" :loading="loading"
        @submit="submitOwnerDocumentsAndTags" />
      <layout-loading v-else />
    </div>

    <div v-if="currentPage == 5" class="register-step__completed">
      <h2>Inscription complétée!</h2>

      <div class="register-step__completed__text">
        <p>
          Votre inscrition sera validée par un membre de l'équipe et vous aurez alors accès à
          toutes les fonctionnalités de LocoMotion.
        </p>

        <p v-if="!!item.owner">
          En attendant, vous pouvez commencer à entrer les informations sur vos véhicules.
        </p>

        <div class="register-step__completed__button">
          <b-button variant="primary" to="/app">Revenir à l'accueil</b-button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Authenticated from '@/mixins/Authenticated';
import Notification from '@/mixins/Notification';

import CommunityProofForm from '@/components/Community/ProofForm.vue';
import ProfileForm from '@/components/Profile/Form.vue';
import RegisterIntentForm from '@/components/Register/IntentForm.vue';

import FormMixin from '@/mixins/FormMixin';

import { extractErrors } from '@/helpers';

export default {
  name: 'RegisterStep',
  mixins: [Authenticated, FormMixin, Notification],
  components: {
    CommunityProofForm,
    RegisterIntentForm,
    ProfileForm,
  },
  beforeRouteEnter(to, from, next) {
    next((vm) => {
      if (vm.isLoggedIn) {
        if (!vm.isRegistered) {
          if (vm.$route.path !== '/register/2') {
            vm.$router.replace('/register/2');
          }
        } else if (vm.user.communities.length === 0) {
          if (vm.$route.path !== '/register/map') {
            vm.$router.replace('/register/map');
          }
        } else if (!vm.user.communities.reduce((acc, c) => acc && !!c.proof, true)) {
          if (vm.$route.path !== '/register/3') {
            vm.$router.replace('/register/3');
          }
        } else if (!vm.hasCompletedRegistration) {
          vm.$router.replace('/register/4');
        } else if (vm.$route.path !== '/register/5') {
          vm.$router.replace('/register/5');
        }
      }
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
    hasAllProofs() {
      return this.item.communities.reduce((acc, c) => acc && !!c.proof, true);
    },
  },
  methods: {
    async submitAndReload() {
      try {
        await this.submit();

        this.$store.commit('user', this.item);

        if (!this.item.communities || this.item.communities.length === 0) {
          this.$store.commit('addNotification', {
            content: 'Il est temps de choisir une première communauté!',
            title: 'Profil mis-à-jour',
            variant: 'success',
            type: 'register',
          });

          this.$router.push('/register/map');
        } else {
          this.$router.push('/register/3');
        }
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
    async submitCommunityProof() {
      if (!this.hasAllProofs) {
        this.$store.commit('addNotification', {
          content: 'Fournissez toutes les preuves requises.',
          title: 'Données incomplètes',
          variant: 'warning',
          type: 'register',
        });
      } else {
        try {
          await this.submit();

          this.$router.push('/register/4');
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
      }
    },
    async submitOwnerDocumentsAndTags() {
      try {
        await this.submit();
        await this.$store.dispatch('submitUser');

        this.$router.push('/register/5');
      } catch (e) {
        this.$store.commit('addNotification', {
          content: 'Erreur fatale',
          title: "Erreur d'inscription",
          variant: 'danger',
          type: 'register',
        });
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
  margin: 50px auto;

  .register-step__title {
    text-align: center;
  }

  h2 {
    text-align: center;
  }

  .community-proof-form {
    margin-bottom: 2em;
  }

  &__completed__button {
    text-align: center;
  }
}
</style>
